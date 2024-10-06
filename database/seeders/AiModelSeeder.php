<?php

namespace Database\Seeders;

use App\Models\AiModel;
use Illuminate\Database\Seeder;

class AiModelSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $models = [
            ['name' => 'Claude 3.5', 'type' => 'conversation', 'version' => 'claude-3-5-sonnet-20240620'],
        ];

        foreach ($models as $model) {
            $aiModel = new AiModel($model);
            $aiModel->save();
        }
    }
}
