<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SimpleUserResource;
use App\Models\User;
use Illuminate\Http\Request;

class SimpleUserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->role_id, function($q) use ($request) {
                return $q->role($request->role_id);
            })
            ->when($request->status, function($q) use ($request) {
                return $q->where('status', $request->status);
            })
            ->when($request->order_column, function($q) use ($request) {
                return $q->orderBy($request->order_column, $request->order_type ?? 'asc');
            })
            ->get();

        return SimpleUserResource::collection($users);
    }
}
