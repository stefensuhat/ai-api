<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserMetaData;
use App\Models\UserToken;
use App\Services\Supabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    protected Supabase $supabase;

    public function __construct()
    {
        $this->supabase = new Supabase();
    }

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

            // return response()->json(compact('response'));
            // save to user table
            $user = User::where('email', $response->email)->first();

            if (! $user) {
                $user = new User(['email' => $response->email, 'phone' => $response->phone ?? null, 'name' => $response->name]);
                $user->email_verified_at = now();
                $user->save();
            }

            $meta = $user->metadata;

            if (! $meta) {
                $meta = new UserMetadata();
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

            $token = new UserToken();
            $token->user()->associate($user);
            $token->amount = 2;
            $token->save();

            $token = $user->createToken($user->id)->plainTextToken;

            DB::commit();

            $data = ['token' => $token, 'user' => $user->withoutRelations()];

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
