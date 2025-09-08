<?php

namespace App\Http\Controllers;

use App\Models\FavoriteVeterinarian;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class FavoriteVeterinarianController extends Controller
{
    /**
     * Obtener veterinarios favoritos del usuario
     */
    public function index(): JsonResponse
    {
        try {
            $favorites = FavoriteVeterinarian::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'message' => 'Favoritos obtenidos correctamente',
                'favorites' => $favorites
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener favoritos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agregar veterinario a favoritos
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validatedData = $request->validate([
                'place_id' => 'required|string',
                'name' => 'required|string|max:255',
                'address' => 'nullable|string',
                'phone' => 'nullable|string',
                'rating' => 'nullable|numeric|between:0,5',
                'total_ratings' => 'nullable|integer|min:0',
                'business_status' => 'nullable|string',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'photo_url' => 'nullable|string',
                'types' => 'nullable|string'
            ]);

            // Verificar si ya existe
            $existing = FavoriteVeterinarian::where('user_id', auth()->id())
                ->where('place_id', $validatedData['place_id'])
                ->first();

            if ($existing) {
                return response()->json([
                    'message' => 'Este veterinario ya está en favoritos',
                    'veterinarian' => $existing
                ], 409);
            }

            // Crear nuevo favorito
            $favorite = FavoriteVeterinarian::create([
                'user_id' => auth()->id(),
                ...$validatedData
            ]);

            return response()->json([
                'message' => 'Veterinario agregado a favoritos',
                'veterinarian' => $favorite
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al agregar a favoritos',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remover veterinario de favoritos
     */
    public function destroy(string $placeId): JsonResponse
    {
        try {
            $favorite = FavoriteVeterinarian::where('user_id', auth()->id())
                ->where('place_id', $placeId)
                ->first();

            if (!$favorite) {
                return response()->json([
                    'message' => 'Veterinario no encontrado en favoritos'
                ], 404);
            }

            $favorite->delete();

            return response()->json([
                'message' => 'Veterinario removido de favoritos'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al remover de favoritos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}