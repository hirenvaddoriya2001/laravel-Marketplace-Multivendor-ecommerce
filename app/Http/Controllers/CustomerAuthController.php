<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class CustomerAuthController extends Controller
{
    public function showRegister(): View
    {
        return view('front.pages.customer.register', [
            'pageTitle' => 'Create Account | LARAVECOM',
        ]);
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'] ?? null,
            'password' => $validated['password'],
        ]);

        Auth::guard('web')->login($user);
        $request->session()->regenerate();

        return redirect()
            ->route('customer.profile')
            ->with('success', 'Welcome! Your customer account has been created.');
    }

    public function showLogin(): View
    {
        return view('front.pages.customer.login', [
            'pageTitle' => 'Customer Login | LARAVECOM',
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::guard('web')->attempt($credentials, $remember)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'The email address or password is incorrect.',
                ]);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('customer.profile'))
            ->with('success', 'You are now signed in.');
    }

    public function profile(): View
    {
        return view('front.pages.customer.profile', [
            'pageTitle' => 'My Profile | LARAVECOM',
            'customer' => Auth::guard('web')->user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $customer */
        $customer = Auth::guard('web')->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email,'.$customer->id,
            ],
            'phone' => ['nullable', 'string', 'max:30'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $directory = public_path('images/users/customers');

            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            if (
                $customer->avatar &&
                file_exists($directory.DIRECTORY_SEPARATOR.$customer->avatar)
            ) {
                unlink($directory.DIRECTORY_SEPARATOR.$customer->avatar);
            }

            $filename = 'CUSTOMER_'.$customer->id.'_'.time().'.'
                .$request->file('avatar')->getClientOriginalExtension();

            $request->file('avatar')->move($directory, $filename);
            $validated['avatar'] = $filename;
        }

        $customer->update($validated);

        return back()->with('success', 'Your profile has been updated.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        /** @var User $customer */
        $customer = Auth::guard('web')->user();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)->letters()->numbers(),
            ],
        ]);

        if (! Hash::check($validated['current_password'], $customer->password)) {
            return back()->withErrors([
                'current_password' => 'Your current password is incorrect.',
            ]);
        }

        $customer->update([
            'password' => $validated['password'],
        ]);

        return back()->with('success', 'Your password has been changed.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home-page')
            ->with('success', 'You have been signed out.');
    }
}