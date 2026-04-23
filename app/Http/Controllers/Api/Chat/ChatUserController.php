<?php

namespace App\Http\Controllers\Api\Chat;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreChatUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ChatUserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->orderBy('name');

        if ($request->boolean('exclude_self')) {
            $query->whereKeyNot($request->user()->id);
        }

        return UserResource::collection($query->get());
    }

    public function adminIndex(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', User::class);

        return UserResource::collection(User::query()->orderBy('name')->get());
    }

    public function store(StoreChatUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $temporaryPassword = $request->input('password') ?: Str::password(12);

        $user = User::query()->create([
            'name' => $request->input('name'),
            'email' => strtolower((string) $request->input('email')),
            'role' => $request->input('role'),
            'status' => $request->input('status'),
            'password' => Hash::make($temporaryPassword),
        ]);

        return response()->json([
            'data' => UserResource::make($user),
            'temporary_password' => $request->filled('password') ? null : $temporaryPassword,
        ], 201);
    }
}
