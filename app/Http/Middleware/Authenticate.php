<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson()) {
            return null;
        }

        if ($request->routeIs('admin.*')) {
            session()->flash('fail', 'You must login first.');
            return route('admin.login');
        }

        if ($request->routeIs('seller.*')) {
            session()->flash('fail', 'You must login first.');
            return route('seller.login');
        }

        session()->flash('fail', 'Please sign in to access your account.');

        return route('customer.login');
    }   
}
