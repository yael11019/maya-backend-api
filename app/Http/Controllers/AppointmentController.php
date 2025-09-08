<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Pet;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the user's appointments.
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $query = Appointment::where('user_id', $user->id)->with(['pet', 'veterinarian']);

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->where('appointment_date', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->where('appointment_date', '<=', $request->to_date);
        }

        // Apply scope filters
        if ($request->has('filter')) {
            switch ($request->filter) {
                case 'upcoming':
                    $query->upcoming();
                    break;
                case 'pending':
                    $query->pending();
                    break;
                case 'completed':
                    $query->completed();
                    break;
                case 'cancelled':
                    $query->cancelled();
                    break;
            }
        }

        $appointments = $query->orderBy('appointment_date', 'asc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => AppointmentResource::collection($appointments->items()),
            'pagination' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ]
        ]);
    }

    /**
     * Store a newly created appointment in storage.
     */
    public function store(StoreAppointmentRequest $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $request->validated();

        // Verify the pet belongs to the authenticated user
        $pet = Pet::where('id', $validated['pet_id'])
                  ->where('user_id', $user->id)
                  ->first();

        if (!$pet) {
            return response()->json([
                'success' => false,
                'message' => 'Pet not found or does not belong to user'
            ], 403);
        }

        $validated['user_id'] = $user->id;
        $validated['status'] = 'pending';

        $appointment = Appointment::create($validated);
        $appointment->load(['pet', 'veterinarian']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment created successfully',
            'data' => new AppointmentResource($appointment)
        ], 201);
    }

    /**
     * Display the specified appointment.
     */
    public function show(string $id): JsonResponse
    {
        $user = Auth::user();
        
        $appointment = Appointment::with(['pet', 'veterinarian'])
                                 ->where('id', $id)
                                 ->where('user_id', $user->id)
                                 ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new AppointmentResource($appointment)
        ]);
    }

    /**
     * Update the specified appointment in storage.
     */
    public function update(UpdateAppointmentRequest $request, string $id): JsonResponse
    {
        $user = Auth::user();
        
        $appointment = Appointment::where('id', $id)
                                 ->where('user_id', $user->id)
                                 ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }

        $validated = $request->validated();

        // If pet_id is being updated, verify it belongs to the user
        if (isset($validated['pet_id'])) {
            $pet = Pet::where('id', $validated['pet_id'])
                      ->where('user_id', $user->id)
                      ->first();

            if (!$pet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pet not found or does not belong to user'
                ], 403);
            }
        }

        $appointment->update($validated);
        $appointment->load(['pet', 'veterinarian']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment updated successfully',
            'data' => new AppointmentResource($appointment)
        ]);
    }

    /**
     * Remove the specified appointment from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        
        $appointment = Appointment::where('id', $id)
                                 ->where('user_id', $user->id)
                                 ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }

        $appointment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Appointment deleted successfully'
        ]);
    }

    /**
     * Mark appointment as completed.
     */
    public function markAsCompleted(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        
        $appointment = Appointment::where('id', $id)
                                 ->where('user_id', $user->id)
                                 ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }

        $validated = $request->validate([
            'diagnosis' => 'nullable|string',
            'treatment' => 'nullable|string',
            'next_steps' => 'nullable|string',
            'cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string'
        ]);

        $appointment->markAsCompleted($validated);
        $appointment->load(['pet', 'veterinarian']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment marked as completed',
            'data' => new AppointmentResource($appointment)
        ]);
    }

    /**
     * Mark appointment as cancelled.
     */
    public function markAsCancelled(string $id): JsonResponse
    {
        $user = Auth::user();
        
        $appointment = Appointment::where('id', $id)
                                 ->where('user_id', $user->id)
                                 ->first();

        if (!$appointment) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found'
            ], 404);
        }

        $appointment->markAsCancelled();
        $appointment->load(['pet', 'veterinarian']);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled',
            'data' => new AppointmentResource($appointment)
        ]);
    }
}
