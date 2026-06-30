<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        if (session('admin_logged_in') && session('admin_id')) {
            return redirect()->route('admin.dashboard');
        }

        if (session('user_id')) {
            return redirect()->route('user.dashboard');
        }

        return redirect()->route('login');
    }
}
