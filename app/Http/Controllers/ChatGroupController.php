<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatGroupResource;
use App\Models\ChatGroup;

class ChatGroupController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $chatGroups = ChatGroup::all();

        $collection = ChatGroupResource::collection($chatGroups);

        return response()->json($collection);
    }

    public function show($chatGroupId): \Illuminate\Http\JsonResponse
    {
        $chatGroup = ChatGroup::with('chats')->findOrFail($chatGroupId);

        $chatGroupResource = new ChatGroupResource($chatGroup);

        return response()->json($chatGroupResource);
    }

    public function destroy($chatGroup): \Illuminate\Http\JsonResponse
    {
        $chatGroup = ChatGroup::findOrFail($chatGroup);

        if ($chatGroup->delete()) {
            return response()->json(['message' => 'Chat group deleted successfully'], 200);
        }

        return response()->json(['message' => 'Chat group delete failed'], 400);

    }
}
