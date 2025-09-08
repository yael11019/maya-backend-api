<?php

namespace App\Http\Controllers\Role;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input("search");

        $roles = Role::where("name","ilike","%".$search."%")
            ->orderBy("id", "desc")
            ->get();

        return response()->json([
            "roles" => $roles->map(function($role) {
                return [
                    "id" => $role->id,
                    "name" => $role->name,
                    "created_at" => $role->created_at->format("Y-m-d H:i:s"),
                    "permissions" => $role->permissions,
                    "permissions_pluck" => $role->permissions->pluck("name"),
                ];
            })
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $existingRole = Role::where("name", $request->name)->first();
        if ($existingRole) {
            return response()->json([
                "message" => 403,
                "message_text" => "EL NOMBRE DEL ROL YA EXISTE",
            ]);
        }

        $role = Role::create([
            "name" => $request->name,
            "guard_name" => "api",
        ]);

        foreach ($request->permissions as $key => $permission) {
            $role->givePermissionTo($permission);
        }

        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $existingRole = Role::where("name", $request->name)->where("id", "<>", $id)->first();
        if ($existingRole) {
            return response()->json([
                "message" => 403,
                "message_text" => "EL NOMBRE DEL ROL YA EXISTE",
            ]);
        }

        $role = Role::findOrFail($id);

        $role->update([
            "name" => $request->name,
        ]);

        $role->syncPermissions($request->permissions);

        return response()->json([
            "message" => 200,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $role = Role::findOrFail($id);

        $role->delete();

        return response()->json([
            "message" => 200,
        ]);
    }
}
