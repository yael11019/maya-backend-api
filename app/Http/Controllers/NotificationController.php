<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    // Obtener una notificación individual por ID
    public function show(Request $request, $target_pet_id): JsonResponse
    {
        $notification = Notification::where('target_pet_id', $target_pet_id)
            ->orderBy('created_at', 'desc')
            ->first();
        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }
        $actorPetName = null;
        if ($notification->actor_pet_id) {
            $actorPet = \App\Models\Pet::find($notification->actor_pet_id);
            $actorPetName = $actorPet ? $actorPet->name : null;
        }
        $data = array_merge($notification->toArray(), [
            'actor_pet_name' => $actorPetName
        ]);
        return response()->json(['success' => true, 'data' => $data]);
    }

    // Obtener todas las notificaciones para el usuario autenticado
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $petId = $request->get('pet_id') ?? $user->pet_id ?? null;
        $notifications = Notification::where('target_pet_id', $petId)
            ->orderBy('created_at', 'desc')
            ->get();

        // Agregar nombre de la mascota que hizo la acción
        $notifications = $notifications->map(function ($notification) {
            $actorPetName = null;
            if ($notification->actor_pet_id) {
                $actorPet = \App\Models\Pet::find($notification->actor_pet_id);
                $actorPetName = $actorPet ? $actorPet->name : null;
            }
            return array_merge($notification->toArray(), [
                'actor_pet_name' => $actorPetName
            ]);
        });

        return response()->json(['success' => true, 'data' => $notifications]);
    }

    // Registrar una nueva notificación
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'actor_pet_id' => 'required|exists:pets,id',
            'target_pet_id' => 'required|exists:pets,id',
            'post_id' => 'nullable|exists:social_posts,id',
            'like_id' => 'nullable|exists:post_likes,id',
            'follower_ids' => 'nullable|array',
            'type' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'message' => 'nullable|string',
        ]);
        $notification = Notification::create($data);
        return response()->json(['success' => true, 'data' => $notification], 201);
    }
}
