<?php

namespace App\Http\Controllers;

use App\Http\Resources\Vaccine\VaccineResource;
use App\Models\Vaccination;
use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage; 
use Illuminate\Support\Facades\Log;

class VaccinationController extends Controller
{
    /**
     * Obtener todas las vacunas del usuario autenticado
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            
            // Obtener las mascotas del usuario
            $petIds = Pet::where('user_id', $user->id)->pluck('id');
            
            // Obtener vacunas de las mascotas del usuario
            $vaccinations = Vaccination::with('pet')
                ->whereIn('petId', $petIds)
                ->orderBy('vaccinationDate', 'desc')
                ->get();

            return response()->json([
                'vaccinations' => VaccineResource::collection($vaccinations),
                'total' => $vaccinations->count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las vacunas',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener vacunas de una mascota específica
     */
    public function getByPet(Pet $pet): JsonResponse
    {
        try {
            // Verificar que la mascota pertenezca al usuario
            if ($pet->user_id !== auth()->id()) {
                return response()->json(['message' => 'No autorizado'], 403);
            }

            $vaccinations = $pet->vaccinations()
                ->orderBy('vaccinationDate', 'desc')
                ->get();

            return response()->json([
                'vaccinations' => VaccineResource::collection($vaccinations),
                'pet' => [
                    'id' => $pet->id,
                    'name' => $pet->name,
                    'species' => $pet->species
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las vacunas de la mascota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Log para debug
            Log::info('Store vaccination request:', [
            'has_image' => $request->hasFile('vaccineImage'),
            'request_data' => $request->except(['vaccineImage']),
            'content_type' => $request->header('Content-Type'),
            'all_data' => $request->all()
        ]);

        $validatedData = $request->validate([
            'petId' => 'required|integer|exists:pets,id',
            'vaccineName' => 'required|string|max:255',
            'vaccineType' => 'required|in:vaccine,desparasitante',
            'vaccineStatus' => 'required|in:completed,pending',
            'vaccinationDate' => 'nullable|date',
            'vaccineNextDate' => 'nullable|date',
            'vaccineLot' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'vaccineImage' => 'nullable|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
        ]);

        // Verificar que la mascota pertenezca al usuario autenticado
        $pet = Pet::findOrFail($validatedData['petId']);
        if ($pet->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Manejar la imagen si se envió
        $imagePath = null;
        if ($request->hasFile('vaccineImage') && $request->file('vaccineImage')->isValid()) {
            $image = $request->file('vaccineImage');
            
            Log::info('Processing image:', [
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'size' => $image->getSize()
            ]);
            
            // Crear directorio si no existe
            if (!Storage::disk('public')->exists('vaccinations')) {
                Storage::disk('public')->makeDirectory('vaccinations');
            }
            
            // Guardar imagen
            $imageName = 'vaccination_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('vaccinations', $imageName, 'public');
            
            Log::info('Image saved:', ['path' => $imagePath]);
        }

        // Crear la vacuna
        $vaccination = Vaccination::create([
            'petId' => $validatedData['petId'],
            'vaccineName' => $validatedData['vaccineName'],
            'vaccineType' => $validatedData['vaccineType'],
            'vaccineStatus' => $validatedData['vaccineStatus'],
            'vaccinationDate' => $validatedData['vaccinationDate'] ?? null,
            'vaccineNextDate' => $validatedData['vaccineNextDate'] ?? null,
            'vaccineLot' => $validatedData['vaccineLot'] ?? null,
            'notes' => $validatedData['notes'] ?? null,
            'vaccineImage' => $imagePath,
        ]);

        $this->createNextVaccination($vaccination, $request);

        $vaccination->load('pet');

    Log::info('Vaccination created successfully:', [
            'id' => $vaccination->vaccinationId,
            'image' => $vaccination->vaccineImage
        ]);

        return response()->json([
            'message' => 'Vacuna creada correctamente',
            'vaccination' => new VaccineResource($vaccination)
        ], 201);

    } catch (ValidationException $e) {
    Log::error('Validation error:', $e->errors());
        return response()->json([
            'message' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
    Log::error('Store error:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'message' => 'Error al crear la vacuna',
            'error' => $e->getMessage()
        ], 500);
    }
}

/**
 * Actualizar una vacuna
 */
public function update(Request $request, Vaccination $vaccination): JsonResponse
{
    try {
        // Verificar que la vacuna pertenezca a una mascota del usuario
        if ($vaccination->pet->user_id !== auth()->id()) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        // Log para debug
        Log::info('Update vaccination request:', [
            'vaccination_id' => $vaccination->vaccinationId,
            'current_image' => $vaccination->vaccineImage,
            'has_image' => $request->hasFile('vaccineImage'),
            'request_data' => $request->except(['vaccineImage']),
            'content_type' => $request->header('Content-Type')
        ]);

        $validatedData = $request->validate([
            'vaccineName' => 'sometimes|string|max:255',
            'vaccinationDate' => 'sometimes|date',
            'vaccineNextDate' => 'nullable|date',
            'vaccineLot' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'vaccineStatus' => 'sometimes|in:completed,pending',
            'vaccineType' => 'sometimes|in:vaccine,desparasitante',
            'vaccineImage' => 'sometimes|file|image|mimes:jpeg,png,jpg,gif,webp|max:5120'
        ]);

        // Manejar la imagen si se envió
        if ($request->hasFile('vaccineImage') && $request->file('vaccineImage')->isValid()) {
            $image = $request->file('vaccineImage');
            
            Log::info('Processing image update:', [
                'original_name' => $image->getClientOriginalName(),
                'mime_type' => $image->getMimeType(),
                'size' => $image->getSize()
            ]);
            
            // Eliminar imagen anterior si existe
            if ($vaccination->vaccineImage) {
                Storage::disk('public')->delete($vaccination->vaccineImage);
                Log::info('Previous image deleted:', ['path' => $vaccination->vaccineImage]);
            }
            
            // Crear directorio si no existe
            if (!Storage::disk('public')->exists('vaccinations')) {
                Storage::disk('public')->makeDirectory('vaccinations');
            }
            
            // Guardar nueva imagen
            $imageName = 'vaccination_' . $vaccination->vaccinationId . '_' . time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('vaccinations', $imageName, 'public');
            
            $validatedData['vaccineImage'] = $imagePath;
            
            Log::info('New image saved:', ['path' => $imagePath]);
        }

        // Actualizar la vacuna
        $vaccination->update($validatedData);
        
        // Crear automáticamente la próxima vacuna si se completó
        if (isset($validatedData['vaccineStatus']) && $validatedData['vaccineStatus'] === 'completed') {
            $this->createNextVaccination($vaccination, $request);
        }

        $vaccination->refresh();
        $vaccination->load('pet');
        $vaccination->load('pet');

        Log::info('Vaccination updated successfully:', [
            'vaccination_id' => $vaccination->vaccinationId,
            'updated_image' => $vaccination->vaccineImage
        ]);

        return response()->json([
            'message' => 'Vacuna actualizada correctamente',
            'vaccination' => new VaccineResource($vaccination)
        ]);

    } catch (ValidationException $e) {
        Log::error('Update validation error:', $e->errors());
        return response()->json([
            'message' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Update error:', [
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'message' => 'Error al actualizar la vacuna',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Mostrar una vacuna específica
     */
    public function show(Vaccination $vaccination): JsonResponse
    {
        try {
            // Verificar que la vacuna pertenezca a una mascota del usuario
            if ($vaccination->pet->user_id !== auth()->id()) {
                return response()->json(['message' => 'No autorizado'], 403);
            }

            $vaccination->load('pet');

            return response()->json([
                'vaccination' => new VaccineResource($vaccination)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener la vacuna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar una vacuna
     */
    public function destroy(Vaccination $vaccination): JsonResponse
    {
        try {
            // Verificar que la vacuna pertenezca a una mascota del usuario
            if ($vaccination->pet->user_id !== auth()->id()) {
                return response()->json(['message' => 'No autorizado'], 403);
            }

            $vaccination->delete();

            return response()->json([
                'message' => 'Vacuna eliminada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar la vacuna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

/**
 * Crear automáticamente la próxima vacuna basada en frecuencia o fecha
 */
private function createNextVaccination(Vaccination $vaccination, Request $request)
{
    try {
        $nextDate = null;
        $frequency = $request->input('frequency');

        // Si hay frecuencia definida, calcular próxima fecha
        if ($frequency) {
            $nextDate = $this->calculateNextDate($vaccination, $frequency);
        } elseif ($vaccination->vaccineNextDate) {
            // Si hay fecha específica de próxima vacuna
            $nextDate = $vaccination->vaccineNextDate;
        }

        // Solo crear si hay una fecha futura
        if ($nextDate && $nextDate > now()) {
            Log::info('Creating next vaccination:', [
                'parent_vaccination_id' => $vaccination->vaccinationId,
                'next_date' => $nextDate,
                'frequency' => $frequency
            ]);

            $nextVaccination = Vaccination::create([
                'petId' => $vaccination->petId,
                'vaccineName' => $vaccination->vaccineName,
                'vaccineType' => $vaccination->vaccineType,
                'vaccineStatus' => 'pending',
                'vaccinationDate' => null,
                'vaccineNextDate' => $nextDate,
                'vaccineLot' => null,
                'notes' => $this->generateNextVaccineNotes($vaccination, $frequency),
                'vaccineImage' => null,
                'parent_vaccination_id' => $vaccination->vaccinationId, // Referencia a la vacuna padre
            ]);

            Log::info('Next vaccination created successfully:', [
                'next_vaccination_id' => $nextVaccination->vaccinationId
            ]);
        }

    } catch (\Exception $e) {
        Log::error('Error creating next vaccination:', [
            'error' => $e->getMessage(),
            'vaccination_id' => $vaccination->vaccinationId
        ]);
        // No lanzar error para no interrumpir el flujo principal
    }
}

/**
 * Calcular la próxima fecha basada en la frecuencia
 */
private function calculateNextDate(Vaccination $vaccination, string $frequency): ?Carbon
{
    $baseDate = $vaccination->vaccinationDate ?? $vaccination->vaccineNextDate ?? now();
    
    if (!$baseDate instanceof Carbon) {
        $baseDate = Carbon::parse($baseDate);
    }

    switch ($frequency) {
        case '1_month':
            return $baseDate->copy()->addMonth();
        case '3_months':
            return $baseDate->copy()->addMonths(3);
        case '6_months':
            return $baseDate->copy()->addMonths(6);
        case '1_year':
            return $baseDate->copy()->addYear();
        default:
            return null;
    }
}

/**
 * Generar notas para la próxima vacuna
 */
private function generateNextVaccineNotes(Vaccination $vaccination, ?string $frequency): string
{
    $notes = "Vacuna programada automáticamente";
    
    if ($frequency) {
        $frequencyLabels = [
            '1_month' => 'mensual',
            '3_months' => 'trimestral',
            '6_months' => 'semestral',
            '1_year' => 'anual'
        ];
        
        $frequencyLabel = $frequencyLabels[$frequency] ?? $frequency;
        $notes .= " - Frecuencia: {$frequencyLabel}";
    }
    
    $notes .= "\nVacuna anterior aplicada: " . ($vaccination->vaccinationDate ?? 'N/A');
    
    return $notes;
}
}