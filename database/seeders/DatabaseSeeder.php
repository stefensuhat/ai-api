<?php

namespace Database\Seeders;

use App\Models\AiModel;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\PricingPlan;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // if (User::count() == 0) {
        //     $user = new User;
        //     $user->name = 'Stefen Suhat';
        //     $user->email = 'stefensuhat@gmail.com';
        //     $user->password = bcrypt('password');
        //     $user->email_verified_at = now();
        //     $user->save();
        // }

        // $user = new User;
        // $user->name = fake()->name;
        // $user->email = fake()->email;
        // $user->password = bcrypt('password');
        // $user->email_verified_at = now();
        // $user->save();

        // $this->call(ChatSeeder::class);
        $settings = [
            [
                'key' => 'pricePerCredit',
                'value' => 100,
            ],
            [
                'key' => 'usdToIdr',
                'value' => 16500,
            ],
            [
                'key' => 'aspectRatio',
                'value' => [
                    ['key' => 'square', 'name' => 'Square', 'value' => ['width' => 1024, 'height' => 1024]],
                    ['key' => 'wide', 'name' => 'Wide', 'value' => ['width' => 1200, 'height' => 675]],
                    ['key' => 'portrait', 'name' => 'Portrait', 'value' => ['width' => 675, 'height' => 1200]],
                ],
            ],
        ];

        foreach ($settings as $setting) {

            if (is_array($setting['value'])) {
                foreach ($setting['value'] as $key => $value) {

                    $getSetting = Setting::where('key', $setting['key'])->first();

                    if (! $getSetting) {
                        $setting = new Setting($setting);
                        $setting->key = $setting['key'];
                        $setting->value = json_encode($setting['value']);
                        $setting->save();
                    }
                }

            }
            $getSetting = Setting::where('key', $setting['key'])->first();

            if (! $getSetting) {
                $setting = new Setting($setting);
                $setting->save();
            }

        }

        // $models = [
        //     [
        //         'id' => '01hznk8m71wyc3p0rhy31pn8cc',
        //         'name' => 'Diffusion',
        //         'version' => 'stability-ai/stable-diffusion-3',
        //         'preview_url' => 'https://replicate.delivery/pbxt/sWeZFZou6v3CPKuoJbqX46ugPaHT1DcsWYx0srPmGrMOCPYIA/out-0.png',
        //     ],
        //     [
        //         'id' => '01hznk8m73dtyc68xadv2nqp64',
        //         'name' => 'Person',
        //         'version' => 'bytedance/sdxl-lightning-4step:5f24084160c9089501c1b3545d9be3c27883ae2239b6f412990e82d4a6210f8f',
        //         'preview_url' => 'https://replicate.delivery/pbxt/dYdYGKKt04pHJ1kle3eStm3q4mfPiUFlQ5xGeM3mfboYbMPUC/out-0.png',
        //     ],
        // ];
        //
        // foreach ($models as $model) {
        //     $aiModel = new AiModel($model);
        //     $aiModel->version = $model['version'];
        //     $aiModel->save();
        // }
        //
        // $basePrice = 5000;
        //
        // $plans = [
        //     [
        //         'name' => 'Starter',
        //         'value' => 10,
        //         'discount' => 0,
        //         'description' => 'User who just wants to try.',
        //     ],
        //     [
        //         'name' => 'Explorer',
        //         'value' => 50,
        //         'discount' => 20,
        //         'description' => 'User who likes to explore.',
        //     ],
        //     [
        //         'name' => 'Pro',
        //         'description' => 'Power users, for professionals.',
        //         'value' => 100,
        //         'discount' => 30,
        //     ],
        // ];
        //
        // foreach ($plans as $plan) {
        //     $actualPrice = $basePrice * $plan['value'];
        //     $discountedPrice = $actualPrice - $actualPrice * ($plan['discount'] / 100);
        //
        //     $pricePlan = new PricingPlan($plan);
        //     $pricePlan->subtotal = $actualPrice;
        //     $pricePlan->grand_total = $discountedPrice;
        //     $pricePlan->is_active = true;
        //     $pricePlan->save();
        // }

    }
}
