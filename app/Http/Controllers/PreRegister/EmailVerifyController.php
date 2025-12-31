<?php

namespace App\Http\Controllers\PreRegister;

use App\Http\Controllers\Controller;
use App\Models\PreUser;
use Inertia\Inertia;

class EmailVerifyController extends Controller
{
    public function verify(string $token)
    {
        $preUser = PreUser::where('token', $token)
            ->where('expires_at', '>', now())
            ->firstOrFail();

        if (! $preUser->isVerified()) {
            $preUser->verify();
        }

        return Inertia::render('PreRegister/Verified', [
            'email' => $preUser->email,
            'token' => $preUser->token,
        ]);
    }
}

