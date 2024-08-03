<?php

namespace App\Http\Controllers;

use App\Http\Resources\ChatGroupResource;
use App\Models\Chat;
use App\Models\ChatGroup;
use App\Models\ChatLog;
use App\Models\Setting;
use App\Models\UserCreditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function index(Request $request): \Illuminate\Http\JsonResponse
    {
        $orderBy = $request->query('orderBy');
        $orderDir = $request->query('orderDir', 'desc');
        $count = $request->query('count', 100);

        $chats = ChatGroup::when($orderBy, fn ($query) => $query->orderBy($orderBy, $orderDir))
            ->take($count)
            ->get();

        $chatGroups = ChatGroupResource::collection($chats);

        return response()->json($chatGroups);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'chat_group_id' => 'nullable|ulid|exists:chat_groups,id',
            'prompt' => 'required',
            'user.role' => 'required|string',
            'user.content' => 'required',
            'user.type' => 'required_if:role,assistant|string',
            'assistant.role' => 'required|string',
            'assistant.content' => 'required',
            'assistant.type' => 'required_if:role,assistant|string',
            'assistant.model' => 'required_if:role,assistant|string',
            'assistant.id' => 'required_if:role,assistant|string', // msg_id
        ]);

        if ($validated) {
            $user = $request->user();

            $inputTokens = $request->input('assistant.usage.input_tokens');
            $outputTokens = $request->input('assistant.usage.output_tokens');

            $usdToIdr = Setting::where('key', 'usdToIdr')->value('value');
            $totalCost = round((($inputTokens + $outputTokens) * $usdToIdr) / 1000000, 2);

            if ($totalCost > $user->credit->amount) {
                return response()->json(['error' => 'Insufficient credit'], 400);
            }

            DB::beginTransaction();

            try {
                // save group if there is no group
                $chatGroup = new ChatGroup;
                if ($request->input('chat_group_id')) {
                    $chatGroup = ChatGroup::find($request->input('chat_group_id'));

                    if (! $chatGroup) {
                        DB::rollBack();

                        return response()->json(['error' => 'Chat group not found'], 400);
                    }
                    $chatGroup->touch();

                } else {
                    $chatGroup->name = $request->input('prompt');
                    $chatGroup->user()->associate($user);
                    $chatGroup->save();
                }

                // save chats
                $chat = new Chat;
                $chat->user()->associate($user);
                $chat->chatGroup()->associate($chatGroup);

                $clone = clone $chat;

                $chat->role = $request->input('user.role');
                $chat->content = json_encode($request->input('user.content'));
                $chat->save();

                $clone->role = $request->input('assistant.role');
                $clone->content = json_encode($request->input('assistant.content'));
                $clone->save();

                // save chat logs
                if ($request->input('assistant')) {
                    $log = new ChatLog($request->input('assistant'));
                    $log->chat_id = $chat->id;
                    $log->user()->associate($user);
                    $log->msg_id = $request->input('assistant.id');
                    $log->input_tokens = $request->input('assistant.usage.input_tokens');
                    $log->output_tokens = $request->input('assistant.usage.output_tokens');
                    $log->save();

                    // handle user credit
                    $userCredit = $user->credit;

                    $userCredit->amount = round($userCredit->amount - $totalCost, 2);
                    $userCredit->save();

                    $creditLog = new UserCreditLog;
                    $creditLog->userCredit()->associate($userCredit);
                    $creditLog->loggable()->associate($clone);
                    $creditLog->amount = $totalCost;
                    $creditLog->save();
                }

                DB::commit();

                $resource = ChatGroupResource::make($chatGroup);

                return response()->json($resource, 201);
            } catch (\Exception $e) {
                DB::rollBack();

                throw $e;

                return response()->json($e->getMessage(), 500);

            }
        }

        return response()->json(400, 400);
    }
}
