<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Http\Controllers\Controller;
use App\Http\Middleware\SetApiLocale;
use App\Models\DeviceToken;

class DeviceTokenController extends Controller
{
    /**
     * Guests can register too (no login needed). If a Bearer token is sent,
     * the device is linked to that user; otherwise it is stored without a user.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'token'        => ['required', 'string', 'max:255'],
            'platform'     => ['nullable', Rule::in(['android', 'ios'])],
            'language'     => ['nullable', Rule::in(SetApiLocale::SUPPORTED)],
            'country_code' => ['nullable', 'string', 'size:2'],
        ]);

        DeviceToken::updateOrCreate(
            ['token' => $data['token']],
            [
                'user_id'      => $request->user('sanctum')?->id,
                'platform'     => $data['platform'] ?? null,
                'language'     => $data['language'] ?? app()->getLocale(),
                'country_code' => isset($data['country_code']) ? strtoupper($data['country_code']) : null,
            ]
        );

        return response()->noContent();
    }

    /** Call this when the user logs out, so push messages stop reaching that account. */
    public function destroy(Request $request)
    {
        $token = $request->validate(['token' => ['required', 'string', 'max:255']])['token'];

        DeviceToken::where('token', $token)->delete();

        return response()->noContent();
    }
}