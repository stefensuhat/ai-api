<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatGroupResource;
use App\Models\Chat;
use App\Models\ChatGroup;
use App\Models\ChatLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $orderBy = $request->query('order_by');
        $count = $request->query('count', 100);

        $chats = ChatGroup::latest('updated_at')
            ->when($orderBy, function ($query) use ($orderBy) {
                return $query->orderBy($orderBy, 'desc');
            })
            ->take($count)
            ->get();

        $chatGroups = ChatGroupResource::collection($chats);

        return response()->json($chatGroups);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'chat_group_id' => 'ulid',
            'role' => 'required|string',
            'content' => 'required|json',
        ]);

        if ($validated) {
            DB::beginTransaction();

            $user = $request->user();

            try {
                // save group if there is no group
                $chatGroup = new ChatGroup;
                if ($request->input('chat_group_id')) {
                    $chatGroup = ChatGroup::find($request->input('chat_group_id'));
                    $chatGroup->touch();

                    if (! $chatGroup) {
                        DB::rollBack();

                        return response()->json(['error' => 'Chat group not found'], 400);
                    }
                } else {
                    $chatGroup->name = $request->input('group_name');
                    $chatGroup->user()->associate($user);
                    $chatGroup->save();
                }

                // save chats
                $chat = new Chat;
                $chat->chatGroup()->associate($chatGroup);
                $chat->role = $request->input('role');
                $chat->content = $request->input('content');
                $chat->save();

                // save chat logs
                $log = new ChatLog($request->all());
                $log->chat()->associate($chat);
                $log->user()->associate($user);
                $log->msg_id = $request->input('msg_id');
                $log->save();

                return response()->json(200, 201);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json($e->getMessage(), 500);

            }
        }

        return response()->json(400, 400);
    }
}
