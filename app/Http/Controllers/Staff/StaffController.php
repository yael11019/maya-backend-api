<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\User\UserCollection;
use App\Http\Resources\User\UserResource;
use Spatie\Permission\Models\Role;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');

        $users = User::where("name", "ilike", "%" . $search . "%")
            ->orderBy("id", "desc")
            ->get();
        
        return response()->json([
            "users" => UserCollection::make($users),
            "roles" => Role::whereRaw("name not ilike ?", ['%veterinario%'])->get()->map(function($role){
                return [
                    "id" => $role->id,
                    "name" => $role->name,
                ];
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $userExists = User::where("email", $request->email)->first();
        if ($userExists) {
            return response()->json([
                "message" => 403,
                "message_text" => "El usuario ya existe",
            ]);
        }
        
        if ($request->hasFile('image')) {
            $path = Storage::putFile("users", $request->file("image"));
            $request->request->add([
                "avatar" => $path,
            ]);
        }

        if($request->password) {
            $request->request->add([
                "password" => bcrypt($request->password),
            ]);
        }

        $user = User::create($request->all());
        $role = Role::findOrFail($request->role_id);
        $user->assignRole($role);

        return response()->json([
            "message" => 200,
            "user" => UserResource::make($user),
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
        $userExists = User::where("email", $request->email)->where("id", "<>", $id)->first();
        if ($userExists) {
            return response()->json([
                "message" => 403,
                "message_text" => "El usuario ya existe",
            ]);
        }

        $user = User::findOrFail($id);
        
        if ($request->hasFile('image')) {
            if($user->avatar) {
                Storage::delete($user->avatar);
            }
            $path = Storage::putFile("users", $request->file("image"));
            $request->request->add([
                "avatar" => $path,
            ]);
        }

        if($request->password) {
            $request->request->add([
                "password" => bcrypt($request->password),
            ]);
        }
        
        $user->update($request->all());
        if ($request->role_id && $request->role_id != $user->role_id) {
            $old_role = Role::findOrFail($user->role_id);
            $user->removeRole($old_role);

            $new_role = Role::findOrFail($request->role_id);
            $user->assignRole($new_role);
        }
        

        return response()->json([
            "message" => 200,
            "user" => UserResource::make($user),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        if($user->avatar) {
            Storage::delete($user->avatar);
        }
        $user->delete();

        return response()->json([
            "message" => 200,
        ]);
    }
}
