<?php

namespace App\Http\Controllers;

use App\Helpers\Claude;
use App\Http\Resources\ChatGroupResource;
use App\Models\AiModel;
use App\Models\Chat;
use App\Models\ChatGroup;
use App\Models\ChatLog;
use App\Models\Prompt;
use App\Models\Setting;
use App\Models\UserCreditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ConversationController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'prompt' => 'required',
            'tone' => 'required',
            'model' => 'required|exists:ai_models,version',
            'prompt_type' => 'required|exists:prompts,key',
        ]);

        if ($validated) {
            $user = $request->user();

            //data format: {
            //     "model": "claude-3-5-sonnet-20240620",
            //     "max_tokens": 1024,
            //     "messages": [
            //         {"role": "user", "content": "Hello, Claude"}
            //     ]
            // }
            $prompt = $request->input('prompt');
            $tone = $request->input('tone', 'casual');
            $additionalMsg =
             'Generate the response with following format: a title based on the request to save to db###the real response.';
            $baseSystemMsg = 'You are an AI email assistant specialized in crafting professional email subjects and descriptions. Your task is to generate compelling and concise email content based on the information provided. Start drafting the email content based on the request.Keep paragraphs short and concise for easy readability.Set the text to bold, italic, or underline as needed.';
            $toneMsg = "Set it with $tone tone";
            $getSetup = AiModel::version($request->input('model'))->first();

            $claude = new Claude($getSetup->version);
            $chatResponse = $claude->chat(
                "$baseSystemMsg $additionalMsg $toneMsg",
                [
                    ['role' => 'user', 'content' => [['type' => 'text', 'text' => $prompt]]],
                ],
            )->throw();

            if (! $chatResponse->successful()) {
                logger()->error($chatResponse->json());

                return response()->json(['message' => 'Failed to generate request'], 500);
            }

            $response = json_decode($chatResponse, true);
            logger()->info('Claude Response: '.$response['content']);

            $inputTokens = $response['usage']['input_tokens'];
            $outputTokens = $response['usage']['output_tokens'];

            $usdToIdr = Setting::where('key', 'usdToIdr')->value('value');
            $totalCost = round((($inputTokens + $outputTokens) * $usdToIdr) / 1000000, 2);

            if ($totalCost > $user->credit->amount) {
                return response()->json(['error' => 'Insufficient credit'], 400);
            }

            DB::beginTransaction();
            try {

                // get to get group name
                $explode = explode('###', $response->content['messages'][0]['text']);
                $groupName = $explode[0];
                $content = $explode[1];

                // save group if there is no group
                $promptType = Prompt::where('key', $request->input('prompt_type'))->first();

                $chatGroup = new ChatGroup;
                $chatGroup->name = $groupName;
                $chatGroup->user()->associate($user);
                $chatGroup->prompt()->associate($promptType);
                $chatGroup->save();

                // save chats
                $chat = new Chat;
                $chat->user()->associate($user);
                $chat->chatGroup()->associate($chatGroup);

                $clone = clone $chat;

                $chat->tone = $request->input('tone', 'casual');
                $chat->role = 'user';
                $chat->content = json_encode(['type' => 'text', 'content' => $prompt]);
                $chat->save();

                $clone->role = $response['role'];
                $clone->content = json_encode(['type' => 'text', 'content' => $content]);
                $clone->save();

                // save chat logs
                if ($request->input('assistant')) {
                    $log = new ChatLog;
                    $log->user()->associate($user);
                    $log->chat_id = $chat->id;
                    $log->model = $response['model'];
                    $log->input_tokens = $inputTokens;
                    $log->output_tokens = $outputTokens;
                    $log->msg_id = $response['id'];
                    $log->save();

                    // handle user credit
                    $userCredit = $user->credit;

                    $amountToReduce = $userCredit->amount - $totalCost;
                    $userCredit->amount = number_format((float) $amountToReduce, 2, '.', '');
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
