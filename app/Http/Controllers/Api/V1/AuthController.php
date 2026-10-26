<?php

namespace App\Http\Controllers\Api\V1;


use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{DB, Hash};
use Illuminate\Validation\{Rule, ValidationException};
use Illuminate\Support\Str;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetApiLocale;
use App\Http\Resources\UserResource;
use App\Models\{SocialAccount, User};
use App\Services\SocialTokenVerifier;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'               => ['required', 'string', 'max:150'],
            'email'              => ['required', 'email', 'max:190', 'unique:users,email'],
            'password'           => ['required', 'string', 'min:8'],
            'country_code'       => ['nullable', 'string', 'size:2'],
            'preferred_language' => ['nullable', Rule::in(SetApiLocale::SUPPORTED)],
            'device_name'        => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::create([
            'name'               => $data['name'],
            'email'              => $data['email'],
            'password'           => $data['password'],   // hashed by the model cast
            'country_code'       => isset($data['country_code']) ? strtoupper($data['country_code']) : null,
            'preferred_language' => $data['preferred_language'] ?? app()->getLocale(),
            'last_active_at'     => now(),
        ]);

        $user->assignRole('user');

        return $this->tokenResponse($user, $data['device_name'] ?? 'mobile', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email'       => ['required', 'email'],
            'password'    => ['required', 'string'],
            'device_name' => ['nullable', 'string', 'max:100'],
        ]);

        $user = User::where('email', $data['email'])->first();

        // social-only accounts have no password
        if (! $user || ! $user->password || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The email or password is incorrect.'],
            ]);
        }

        $user->forceFill(['last_active_at' => now()])->save();

        return $this->tokenResponse($user, $data['device_name'] ?? 'mobile');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function me(Request $request): UserResource
    {
        return new UserResource($request->user());
    }

    public function update(Request $request): UserResource
    {
        $data = $request->validate([
            'name'               => ['sometimes', 'string', 'max:150'],
            'country_code'       => ['sometimes', 'nullable', 'string', 'size:2'],
            'preferred_language' => ['sometimes', Rule::in(SetApiLocale::SUPPORTED)],
        ]);

        if (! empty($data['country_code'])) {
            $data['country_code'] = strtoupper($data['country_code']);
        }

        $request->user()->update($data);

        return new UserResource($request->user()->fresh());
    }

    /** Apple and Google require in-app account deletion. */
    public function destroy(Request $request)
    {
        $user = $request->user();

        if ($user->password) {
            $request->validate(['password' => ['required', 'current_password:sanctum']]);
        }

        $user->tokens()->delete();
        $user->forceDelete();   // trips, saved places, etc. are removed by the foreign keys

        return response()->noContent();
    }

    private function tokenResponse(User $user, string $device, int $status = 200): JsonResponse
    {
        return response()->json([
            'token' => $user->createToken($device)->plainTextToken,
            'user'  => (new UserResource($user))->resolve(),
        ], $status);
    }
    public function social(Request $request, SocialTokenVerifier $verifier): JsonResponse
    {
        $data = $request->validate([
            'provider'           => ['required', Rule::in(['google', 'apple'])],
            'id_token'           => ['required', 'string', 'max:8192'],
            'nonce'              => ['nullable', 'string', 'max:200'],
            'name'               => ['nullable', 'string', 'max:150'],   // Apple only sends the name to the app, on first login
            'country_code'       => ['nullable', 'string', 'size:2'],
            'preferred_language' => ['nullable', Rule::in(SetApiLocale::SUPPORTED)],
            'device_name'        => ['nullable', 'string', 'max:100'],
        ]);

        $identity = $verifier->verify($data['provider'], $data['id_token'], $data['nonce'] ?? null);

        $user = DB::transaction(fn() => $this->resolveSocialUser($data['provider'], $identity, $data));

        $user->forceFill(['last_active_at' => now()])->save();

        return $this->tokenResponse($user, $data['device_name'] ?? 'mobile');
    }

    private function resolveSocialUser(string $provider, array $identity, array $data): User
    {
        $account = SocialAccount::where('provider', $provider)
            ->where('provider_user_id', $identity['sub'])
            ->first();

        if ($account) {
            $user = User::withTrashed()->findOrFail($account->user_id);
        } else {
            $email = ($identity['email'] && $identity['email_verified']) ? strtolower($identity['email']) : null;

            if (! $email) {
                throw ValidationException::withMessages([
                    'id_token' => ['This account has no verified email address.'],
                ]);
            }

            $user = User::withTrashed()->where('email', $email)->first();

            if ($user && ! $user->trashed() && $user->email_verified_at === null) {
                // The password account was never verified, so someone may have registered
                // with this email without owning it. The real owner just proved ownership
                // through the provider: remove the old password and every old session.
                $user->forceFill(['password' => null, 'email_verified_at' => now()])->save();
                $user->tokens()->delete();
            }

            if (! $user) {
                $user = User::create([
                    'name'               => $identity['name'] ?? $data['name'] ?? Str::before($email, '@'),
                    'email'              => $email,
                    'password'           => null,
                    'country_code'       => isset($data['country_code']) ? strtoupper($data['country_code']) : null,
                    'preferred_language' => $data['preferred_language'] ?? app()->getLocale(),
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
                $user->assignRole('user');
            }

            if (! $user->trashed()) {
                $user->socialAccounts()->create([
                    'provider'         => $provider,
                    'provider_user_id' => $identity['sub'],
                ]);
            }
        }

        if ($user->trashed()) {
            abort(403, 'This account is disabled.');
        }

        return $user;
    }
}
