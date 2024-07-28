<?php

namespace App\Http\Controllers;

use App\Enums\AspectRatio;
use App\Models\AiModel;
use App\Models\Result;
use App\Models\User;
use HalilCosdu\Replicate\Facades\Replicate;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function textToImage(Request $request): \Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'model_id' => 'required',
            'prompt' => 'required|string',
            'ratio' => 'required',
            'count' => 'required|numeric|min:1|max:4',
        ]);

        if ($validated) {
            $model = $request->input('model_id');
            $prompt = $request->input('prompt');

            $getModel = AiModel::find($model);

            if (! $getModel) {
                return response()->json(['error' => 'Model not found'], 404);
            }

            $version = $getModel->version;

            try {
                $getAspectRatio = AspectRatio::tryFrom($request->input('ratio'));
            } catch (\Exception $e) {
                return response()->json(['error' => 'Unknown ratio'], 500);
            }

            ['width' => $width, 'height' => $height] = $getAspectRatio->getSize();

            $data = [
                'version' => "$version",
                'input' => [
                    'width' => $width,
                    'height' => $height,
                    'prompt' => $prompt,
                    'scheduler' => 'DPMSolverMultistep',
                    'num_outputs' => $request->input('count'),
                    'guidance_scale' => 7.5,
                    'num_inference_steps' => 60,
                ],
            ];

            $replicate = Replicate::createPrediction($data);

            $response = $replicate->json();

            $result = new Result;
            $result->user_id = User::first()->id;
            $result->model_id = $getModel->id;
            $result->pending_response = $response;
            $result->save();

            return response()->json($result);
        }

        return response()->json(['error' => 'Failed to generate image'], 400);

    }

    public function getImageResult($textToImageId)
    {
        $user = User::first();
        $result = Result::where('user_id', $user->id)->find($textToImageId);

        if (! $result) {
            return response()->json(['error' => 'Result not found'], 404);
        }

        // if image is already saved, return it
        if ($result->image_url) {
            $getUrl = Storage::disk('public')->url($result->image_url);

            return response()->json(['url' => $getUrl]);
        }

        // if image is not saved, but pending response already queried to replicate,
        // process the image and return it
        if ($result->success_response) {
            return $this->processImageResult($result);
        }

        // process pending response
        $getExecutedResponse = $this->executePendingResponse($result);

        if ($getExecutedResponse['error']) {
            return response()->json($getExecutedResponse, 500);
        }

        return $this->processImageResult($getExecutedResponse);
    }

    protected function executePendingResponse(Result $result): array|Result
    {
        $getUrl = $result->pending_response['urls']['get'];

        try {
            $replicateResponse = Http::withToken(config('replicate.api_token'))
                ->withHeaders(['Content-Type' => 'application/json'])
                ->get($getUrl);
        } catch (ConnectionException $e) {
            logger()->error($e->getMessage());

            return ['error' => 'Failed to retrieve image'];
        }

        $result->success_response = $replicateResponse->json();
        $result->response_id = $replicateResponse['id'];
        $result->save();

        return $result;
    }

    protected function processImageResult(Result $result): \Illuminate\Http\JsonResponse
    {
        $successResponse = $result->success_response;

        $imageUrl = $successResponse['output'][0];

        $imageContents = Http::get($imageUrl);

        if ($imageContents->successful()) {

            $content = $imageContents->body();
            $fileInfo = pathinfo($imageUrl);
            $fileName = 'images/'.$result->user_id.'/'.uniqid().".{$fileInfo['extension']}";

            Storage::disk('public')->put($fileName, $content);

            $getImage = Storage::disk('public')->url($fileName);

            $result->image_url = $fileName;
            $result->save();

            return response()->json(['url' => $getImage]);
        }

        return response()->json(['message' => 'Failed to parse image'], 400);
    }
}
