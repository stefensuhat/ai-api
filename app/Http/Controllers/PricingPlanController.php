<?php

namespace App\Http\Controllers;

use App\Http\Resources\PricinPlanResource;
use App\Models\PricingPlan;

class PricingPlanController extends Controller
{
    public function index(): \Illuminate\Http\JsonResponse
    {
        $plans = PricingPlan::all();

        $collection = PricinPlanResource::collection($plans);

        return response()->json($collection);

    }

    public function show($id): \Illuminate\Http\JsonResponse
    {
        $plan = PricingPlan::find($id);
        $resource = PricinPlanResource::make($plan);

        return response()->json($resource);
    }
}
