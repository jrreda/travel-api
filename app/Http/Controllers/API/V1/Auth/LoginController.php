<?php

namespace App\Http\Controllers\API\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginRequest $request)
    {
        $user = User::where('email', $request->get('email'))->first();

        // if (! $user || ! $user->hasVerifiedEmail()) {
        if (! $user || ! Hash::check($request->get('password'), $user->password)) {
            return response()->json(['error' => 'The provided credentials are incorrect.'], 401);
        }

        $device = substr($request->userAgent() ?? '', 0, 255);

        return response()->json([
            'access_token' => $user->createToken($device)->plainTextToken,
        ]);
    }
}
