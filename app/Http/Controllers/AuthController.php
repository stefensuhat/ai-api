<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserCredit;
use App\Models\UserMetaData;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * @throws ConnectionException
     */
    public function loginWithProvider(Request $request)
    {
        $provider = $request->input('provider');
        $token = $request->input('access_token');

        DB::beginTransaction();

        try {
            $response = Socialite::driver($provider)->userFromToken($token);

            if (! $response) {
                return response()->json(['error' => 'Invalid token'], 401);
            }

            // save to user table
            $user = User::where('email', $response->email)->first();

            if (! $user) {
                $user = new User(['email' => $response->email, 'phone' => $response->phone ?? null, 'name' => $response->name]);
                $user->email_verified_at = now();
                $user->save();

                $credit = new UserCredit;
                $credit->user()->associate($user);
                $credit->amount = 75;
                $credit->save();
            }

            $meta = $user->metadata;

            if (! $meta) {
                $meta = new UserMetadata;
                $meta->user()->associate($user);
            }

            if ($provider === 'google') {
                $meta->google_data = json_encode($response->user);
                $meta->google_picture = $response->user['picture'];
            }

            if ($provider === 'github') {
                $meta->github_data = json_encode($response->user);
                $meta->github_avatar = $response->avatar;
            }
            $meta->save();

            $token = $user->createToken($user->id)->plainTextToken;

            DB::commit();

            $data = ['token' => $token, 'user' => $user];

            return response()->json($data);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['error' => $e->getMessage()], 401);
        }

    }

    public function user(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();

        $resource = new UserResource($user);

        return response()->json($resource);
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json(['message' => 'Logout successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

    }
}
