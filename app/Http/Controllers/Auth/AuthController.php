<?php

namespace App\Http\Controllers\Auth;

// Use your application's base controller (recommended)
use App\Http\Controllers\Controller; // Ensure this is imported
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // *** Import Auth Facade for authentication ***
use Illuminate\Support\Facades\Log; // Use Log for debugging

// Extend your application's base controller
class AuthController extends Controller
{
    /**
     * Show the application's login form.
     *
     * @return \Illuminate\View\View
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // *** Add the method to handle the login request ***
    /**
     * Handle a login request to the application.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        // Validate the login request data
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Attempt to log the user in
        // Auth::attempt() attempts to authenticate the user using the provided credentials.
        // It returns true if authentication is successful, false otherwise.
        if (Auth::attempt($credentials)) {
            // Authentication successful...

            // Regenerate the session to prevent session fixation attacks
            $request->session()->regenerate();

            Log::info('User logged in successfully.', ['user_id' => Auth::id(), 'email' => $request->email]);

            // Redirect the user to the intended location or a default location
            // intended() will redirect to the URL the user was trying to access before
            // being redirected to the login page (e.g., /owner/dashboard).
            // If no intended URL, redirect to '/owner/dashboard' as a default.
            return redirect()->intended('/owner/dashboard');

        }

        // Authentication failed...
        Log::warning('Login failed.', ['email' => $request->email, 'ip_address' => $request->ip()]);

        // Redirect back to the login form with an error message and old input
        // You'll need to display these errors in your login.blade.php view
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email'); // Keep only the email input
    }

    // You might add a logout method here later
    // public function logout(Request $request) { ... }
}
