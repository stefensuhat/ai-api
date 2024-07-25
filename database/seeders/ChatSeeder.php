<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatGroup;
use App\Models\ChatLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Hello World',
                    ],
                ],
            ],
            [
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => "Hello! How can I assist you today? Is there anything specific you'd like to talk about or any questions you have?",
                    ],
                ],
            ],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => 'Hello world 2',
                    ],
                ],
            ],
            [
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => "Hello again! It seems you're repeating a common programming phrase. \"Hello, World!\" is often the first program people write when learning a new programming language. Is there something related to programming or computer science you'd like to discuss? Or perhaps you're just saying hello? Either way, I'm here to help if you have any questions or topics you'd like to explore.",
                    ],
                ],
            ],
        ];
        $user = User::first();

        for ($i = 0; $i < 10; $i++) {
            //save chat groups
            // save group if there is no group
            $chatGroup = new ChatGroup;
            $chatGroup->name = fake()->domainName();
            $chatGroup->user()->associate($user);
            $chatGroup->updated_at = now()->addDays(-$i);
            $chatGroup->save();

            foreach ($data as $item) {
                // save chats
                $chat = new Chat($item);
                $chat->chatGroup()->associate($chatGroup);
                $chat->user()->associate($chatGroup);
                $chat->content = json_encode($item['content']);
                $chat->save();

                // save chat logs
                $log = new ChatLog;
                $log->chat_id = $chat->id;
                $log->user()->associate($user);
                $log->model = 'sonnet';
                $log->content = json_encode($item['content']);
                $log->input_tokens = strlen($item['content'][0]['text']);
                $log->output_tokens = strlen($item['content'][0]['text']);
                $log->msg_id = '1234567890';
                $log->save();
            }
        }

    }
}
