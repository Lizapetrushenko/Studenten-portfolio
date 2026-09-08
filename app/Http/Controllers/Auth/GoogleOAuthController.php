<?php

namespace App\Http\Controllers\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class GoogleOAuthController extends Controller {
    public function redirectToGoogle() {
        
        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?client_id=' . env('GOOGLE_CLIENT_ID') . '&redirect_uri=' . env('GOOGLE_REDIRECT_URI') . '&response_type=code&scope=email%20profile');
    }

    public function handleGoogleCallback(Request $request) {
        
    }
}