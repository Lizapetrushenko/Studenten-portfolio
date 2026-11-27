<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TestRoleController extends Controller
{
    public function update(Request $request): RedirectResponse
    {
        abort_unless($request->user()->canSwitchTestRole(), 403);

        $data = $request->validate([
            'role' => ['required', 'string', Rule::in(array_keys(User::TEST_ROLES))],
        ]);

        $request->session()->put('test_account.role', $data['role']);

        return redirect()->route('dashboard');
    }
}
