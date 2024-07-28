<?php

namespace Database\Seeders;

use App\Models\Chat;
use App\Models\ChatGroup;
use App\Models\ChatLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('chats')->truncate();
        DB::table('chat_groups')->truncate();
        DB::table('chat_logs')->truncate();

        $user = User::where('email', 'stefensuhat@gmail.com')->first();

        for ($i = 0; $i < 10; $i++) {
            //save chat groups
            // save group if there is no group
            $chatGroup = new ChatGroup;
            $chatGroup->name = fake()->domainName();
            $chatGroup->user()->associate($user);
            $chatGroup->updated_at = now()->addDays(-$i);
            $chatGroup->save();

            foreach ($this->getChatData() as $item) {
                // save chats
                $chat = new Chat($item);
                $chat->chatGroup()->associate($chatGroup);
                $chat->user()->associate($user);
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

    protected function getChatData()
    {
        $data = [
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => fake()->sentence(10, true),
                    ],
                ],
            ],
            [
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => fake()->sentence(100, true),
                    ],
                ],
            ],
            [
                'role' => 'user',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => fake()->sentence(10, true),
                    ],
                ],
            ],
            [
                'role' => 'assistant',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => fake()->sentence(100, true),
                    ],
                ],
            ],
        ];

        return $data;
    }
}
