<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::query()
            ->when(request('role_id'), function($q) {
                return $q->role(request('role_id'));
            })
            ->when(request('status'), function($q) {
                return $q->where('status', request('status'));
            })
            ->get();

        return UserResource::collection($users);
    }

    public function store(UserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'country_code' => $request->country_code,
            'status' => $request->status,
            'branch_id' => $request->branch_id,
            'password' => Hash::make('password'), // Default password
        ]);

        $user->assignRole($request->role_id);

        return response()->json([
            'message' => 'User created successfully',
            'data' => new UserResource($user)
        ], 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json([
            'data' => new UserResource($user)
        ]);
    }

    public function update(UserRequest $request, User $user): JsonResponse
    {
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'country_code' => $request->country_code,
            'status' => $request->status,
            'branch_id' => $request->branch_id,
        ]);

        $user->syncRoles([$request->role_id]);

        return response()->json([
            'message' => 'User updated successfully',
            'data' => new UserResource($user)
        ]);
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
