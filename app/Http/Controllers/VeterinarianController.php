<?php

namespace App\Http\Controllers;

use App\Models\Veterinarian;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VeterinarianController extends Controller
{
    /**
     * Display a listing of veterinarians.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Veterinarian::query();

        // Search by name
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('veterinarianName', 'like', '%' . $search . '%')
                  ->orWhere('clinicName', 'like', '%' . $search . '%')
                  ->orWhere('city', 'like', '%' . $search . '%');
            });
        }

        // Filter by city
        if ($request->has('city')) {
            $query->byCity($request->city);
        }

        // Simple list for select dropdowns
        if ($request->has('simple') && $request->simple == 'true') {
            $veterinarians = $query->select('veterinarianId', 'veterinarianName', 'clinicName')
                                  ->orderBy('veterinarianName', 'asc')
                                  ->get();
            
            return response()->json([
                'success' => true,
                'data' => $veterinarians->map(function($vet) {
                    return [
                        'id' => $vet->veterinarianId,
                        'name' => $vet->veterinarianName,
                        'clinic' => $vet->clinicName,
                        'label' => $vet->veterinarianName . ' - ' . $vet->clinicName
                    ];
                })
            ]);
        }

        $veterinarians = $query->orderBy('veterinarianName', 'asc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $veterinarians->items(),
            'pagination' => [
                'current_page' => $veterinarians->currentPage(),
                'last_page' => $veterinarians->lastPage(),
                'per_page' => $veterinarians->perPage(),
                'total' => $veterinarians->total(),
            ]
        ]);
    }

    /**
     * Store a newly created veterinarian in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'veterinarianName' => 'required|string|max:255',
            'email' => 'required|email|unique:veterinarians,email',
            'phone' => 'required|string|max:20|unique:veterinarians,phone',
            'streetName' => 'nullable|string|max:255',
            'streetNumber' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zipCode' => 'nullable|string|max:20',
            'clinicName' => 'required|string|max:255',
            'notes' => 'nullable|array'
        ]);

        $veterinarian = Veterinarian::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Veterinarian created successfully',
            'data' => $veterinarian
        ], 201);
    }

    /**
     * Display the specified veterinarian.
     */
    public function show(string $id): JsonResponse
    {
        $veterinarian = Veterinarian::with(['appointments', 'vaccinations'])
                                  ->where('veterinarianId', $id)
                                  ->first();

        if (!$veterinarian) {
            return response()->json([
                'success' => false,
                'message' => 'Veterinarian not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $veterinarian
        ]);
    }

    /**
     * Update the specified veterinarian in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $veterinarian = Veterinarian::where('veterinarianId', $id)->first();

        if (!$veterinarian) {
            return response()->json([
                'success' => false,
                'message' => 'Veterinarian not found'
            ], 404);
        }

        $validated = $request->validate([
            'veterinarianName' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:veterinarians,email,' . $id . ',veterinarianId',
            'phone' => 'sometimes|string|max:20|unique:veterinarians,phone,' . $id . ',veterinarianId',
            'streetName' => 'nullable|string|max:255',
            'streetNumber' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zipCode' => 'nullable|string|max:20',
            'clinicName' => 'sometimes|string|max:255',
            'notes' => 'nullable|array'
        ]);

        $veterinarian->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Veterinarian updated successfully',
            'data' => $veterinarian
        ]);
    }

    /**
     * Remove the specified veterinarian from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $veterinarian = Veterinarian::where('veterinarianId', $id)->first();

        if (!$veterinarian) {
            return response()->json([
                'success' => false,
                'message' => 'Veterinarian not found'
            ], 404);
        }

        $veterinarian->delete();

        return response()->json([
            'success' => true,
            'message' => 'Veterinarian deleted successfully'
        ]);
    }
}
