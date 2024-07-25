<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatGroupResource;

class ChatGroupController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        $chatGroups = ChatGroupResource::collection($user->chatGroups);

        return response()->json($chatGroups, 200);
    }
}
