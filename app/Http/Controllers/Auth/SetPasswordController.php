<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\View\View;

class SetPasswordController extends Controller
{
    public function create(User $user): View
    {
        return view('auth.set-password', ['user' => $user]);
    }
}
