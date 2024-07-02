<?php

namespace App\Http\Controllers;

use App\Http\Resources\AiModelResource;
use App\Models\AiModel;

class AiModelController extends Controller
{
    public function index()
    {
        $aiModel = AiModel::all();

        $collection = AiModelResource::collection($aiModel);

        return response()->json($collection);
    }
}
