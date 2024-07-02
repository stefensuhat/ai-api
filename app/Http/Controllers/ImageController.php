<?php

namespace App\Http\Controllers;

use App\Models\AiModel;
use App\Models\Result;
use App\Models\User;
use HalilCosdu\Replicate\Facades\Replicate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function generateImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $model = $request->input('model');
        $prompt = $request->input('prompt');

        $getModel = AiModel::find($model);
        if (! $getModel) {
            return response()->json(['error' => 'Model not found'], 404);
        }

        $version = $getModel->version;

        //     "input": {
        //     "width": 768,
        //   "height": 768,
        //   "prompt": "an astronaut riding a horse on mars, hd, dramatic lighting",
        //   "scheduler": "K_EULER",
        //   "num_outputs": 1,
        //   "guidance_scale": 7.5,
        //   "num_inference_steps": 51
        // }
        $data = [
            'version' => $version,
            'input' => [
                'width' => 256,
                'height' => 256,
                'prompt' => $prompt,
                'scheduler' => 'K_EULER',
                'num_outputs' => 1,
                'guidance_scale' => 7.5,
                'num_inference_steps' => 51,
            ],
        ];

        $replicate = Replicate::createPrediction($data);

        $response = $replicate->json();

        $result = new Result();
        $result->user_id = User::first()->id;
        $result->model_id = $getModel->id;
        $result->response_id = $response['id'];
        $result->pending_response = $response;
        $result->save();

        return response()->json($result);

        // try {
        //     $data = Http::withToken(env('REPLICATE_API_TOKEN'))
        //         ->withHeaders(['Content-Type' => 'application/json'])
        //         ->get($result['urls']);
        //
        //     return response()->json($data->json());
        // } catch (\Exception $e) {
        //     return response()->json(['error' => $e->getMessage()], 500);
        // }

    }

    public function getImage(Request $request)
    {
        $user = User::first();
        $model = $request->input('model');
        $result = Result::where('user_id', $user->id)
            ->where('model_id', $model)
            ->whereNotNull('success_response')
            ->latest()
            ->first();

        if (! $result) {
            return response()->json(['error' => 'Result not found'], 404);
        }

        $pendingResponse = json_decode($result->pending_response);
        $urlToRetrieve = $pendingResponse->urls->get;

        $finalData = Http::withToken(env('REPLICATE_API_TOKEN'))
            ->withHeaders(['Content-Type' => 'application/json'])
            ->get($urlToRetrieve);

        $result->success_response = $finalData->json();
        $result->response_id = $finalData['id'];
        $result->save();

        // save image to storage

        return response()->json($result);
    }

    public function storeImage()
    {
        $user = User::first();
        $result = Result::where('user_id', $user->id)
            ->whereNotNull('success_response')
            ->latest()
            ->first();

        if ($result->image_url) {
            $getUrl = Storage::disk('public')->url($result->image_url);

            return response()->json(['url' => $getUrl]);
        }

        $successResponse = $result->success_response;

        $imageUrl = $successResponse['output'][0];

        $imageContents = Http::get($imageUrl);

        if ($imageContents->successful()) {

            $content = $imageContents->body();
            $fileInfo = pathinfo($imageUrl);
            $fileName = 'images/'.$user->id.'/'.uniqid().".{$fileInfo['extension']}";

            Storage::disk('public')->put($fileName, $content);

            $result->image_url = $fileName;
            $result->save();

            $getImage = Storage::disk('public')->url($fileName);

            return response()->json(['url' => $getImage]);
        }

        return response()->json(['message' => 'Failed to parse image'], 400);
    }
}
