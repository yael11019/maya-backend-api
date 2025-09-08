<?php

namespace App\Http\Controllers;

use App\Models\Pet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Pets\PetResource;

class PetsController extends Controller
{
    /**
     * Get all pets for the authenticated user
     */
    public function index()
    {
        $user = auth()->user();
        $pets = Pet::where('user_id', $user->id)->get();
        
        return response()->json(
            [
                'pets' => PetResource::collection($pets)
            ]
        );
    }

    /**
     * Get a specific pet
     */
    public function show($id)
    {
        $user = auth()->user();
        $pet = Pet::where('user_id', $user->id)->where('id', $id)->first();
        
        if (!$pet) {
            return response()->json(['error' => 'Pet not found'], 404);
        }
        
        return response()->json(
            [
                'pet' => new PetResource($pet)
            ]
        );
    }

    /**
     * Create a new pet for the authenticated user
     */
    public function store(Request $request)
    {
        // Debug: log del archivo recibido
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            Log::info('Avatar file info:', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'type' => $file->getClientMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'is_valid' => $file->isValid(),
                'error' => $file->getError(),
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'species' => 'required|string|max:255',
            'breed' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'weight' => 'nullable|numeric',
            'gender' => 'required|in:male,female',
            'birth_date' => 'nullable|date',
            'avatar' => 'nullable|file|mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/webp|max:5120'
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed for pet creation:', $validator->errors()->toArray());
            
            // Si la validación falla por el avatar, intentar validación más permisiva
            if ($validator->errors()->has('avatar') && $request->hasFile('avatar')) {
                $avatarValidator = Validator::make($request->only('avatar'), [
                    'avatar' => 'nullable|file|max:5120'
                ]);
                
                if (!$avatarValidator->fails()) {
                    $file = $request->file('avatar');
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mimeType = finfo_file($finfo, $file->getRealPath());
                    finfo_close($finfo);
                    
                    Log::info('File MIME type detection:', [
                        'detected_mime' => $mimeType,
                        'file_mime' => $file->getMimeType(),
                        'extension' => $file->getClientOriginalExtension()
                    ]);
                    
                    // Si es una imagen según finfo, permitir el upload
                    if (str_starts_with($mimeType, 'image/')) {
                        // Continuar con la creación sin validación estricta del avatar
                        Log::info('Avatar validation bypassed - file detected as image via finfo');
                    } else {
                        return response()->json($validator->errors(), 422);
                    }
                } else {
                    return response()->json($validator->errors(), 422);
                }
            } else {
                return response()->json($validator->errors(), 422);
            }
        }

        $user = auth()->user();
        
        $pet = new Pet();
        $pet->user_id = $user->id;
        $pet->name = $request->name;
        $pet->species = $request->species;
        $pet->breed = $request->breed;
        $pet->color = $request->color;
        $pet->weight = $request->weight;
        $pet->gender = $request->gender;
        $pet->birth_date = $request->birth_date;

        if ($request->hasFile('avatar')) {
            $imagePath = $request->file('avatar')->store('pets', 'public');
            $pet->avatar = $imagePath;
        }
        
        $pet->save();

        return response()->json($pet, 201);
    }

    /**
     * Update an existing pet
     */
    public function update(Request $request, $id)
{
    try {
        $pet = Pet::findOrFail($id);
        
        // Validación
        $validatedData = $request->validate([
            'name' => 'sometimes|string|max:255',
            'species' => 'sometimes|string|max:100',
            'breed' => 'sometimes|string|max:100',
            'color' => 'sometimes|string|max:100',
            'weight' => 'sometimes|numeric|min:0|max:200',
            'age' => 'sometimes|integer|min:0|max:30',
            'gender' => 'sometimes|in:male,female',
            'birth_date' => 'sometimes|date|before_or_equal:today',
            'medical_conditions' => 'sometimes|string|nullable',
            'distinctive_traits' => 'sometimes|string|nullable',
            'sterilized' => 'sometimes|boolean'
        ]);
        
        // Actualizar solo los campos enviados
        $pet->update($validatedData);
        
        return response()->json([
            'message' => 'Mascota actualizada correctamente',
            'pet' => $pet->fresh() // Obtener datos actualizados
        ], 200);
        
    } catch (ValidationException $e) {
        return response()->json([
            'message' => 'Error de validación',
            'errors' => $e->errors()
        ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar la mascota',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
        /**
         * Delete a pet
         */
        public function destroy($id)
    {
        $user = auth()->user();
        $pet = Pet::where('user_id', $user->id)->where('id', $id)->first();
        
        if (!$pet) {
            return response()->json(['error' => 'Pet not found'], 404);
        }

        $pet->delete();

        return response()->json(['message' => 'Pet deleted successfully']);
    }
}
