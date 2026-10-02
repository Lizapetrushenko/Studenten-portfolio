<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class GithubOAuthController extends Controller {
   public function redirectToGitHub()
{
    $params = http_build_query([
        'client_id' => env('GITHUB_CLIENT_ID'),
        'redirect_uri' => 'http://127.0.0.1:8000/auth/github/callback',
        'scope' => 'user:email',
    ]);

    return redirect()->away(
        'https://github.com/login/oauth/authorize?' . $params
    );
}
}