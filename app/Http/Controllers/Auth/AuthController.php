<?php

namespace App\Http\Controllers\Auth;

// Use your application's base controller (recommended)
use App\Http\Controllers\Controller; // Ensure this is imported
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // *** Import Auth Facade for authentication ***
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator; // Use Log for debugging

// Use this for validation exceptions
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

    public function logout(Request $request)
    {
        Auth::logout(); // Perform the logout

        $request->session()->invalidate(); // Invalidate the user's session
        $request->session()->regenerateToken(); // Regenerate the CSRF token

        Log::info('User logged out successfully.'); // Log the logout

        // Redirect to the login page or welcome page after logout
        return redirect()->route('login'); // Redirect to the login form route (named 'login')
        // Alternatively, redirect to the welcome page:
        // return redirect('/');
    }

    public function showOwnerRegistrationForm()
    {
        // This method simply returns the view containing the sign-up form for owners
        return view('auth.owner-register'); // *** Return the name of your owner sign-up view file ***
    }

    public function registerOwner(Request $request)
    {
        Log::info('Attempting to register new owner user.');

        // --- Validation for Registration Data ---
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'], // Ensure email is unique in users table
            'password' => ['required', 'string', 'min:8', 'confirmed'], // 'confirmed' requires a password_confirmation field
        ]);

        if ($validator->fails()) {
            Log::warning('Owner registration validation failed.', ['errors' => $validator->errors()->all()]);

            // Redirect back to the registration form with validation errors and old input
            return redirect()->route('owner.register')
                ->withErrors($validator)
                ->withInput();
        }
        Log::info('Owner registration validation passed.');

        try {
            // --- Create the New User Record ---
            // Ensure App\Models\User is imported at the top of the file
            // Ensure Illuminate\Support\Facades\Hash is imported at the top
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), // Hash the password before saving
                'is_owner' => true, // *** Mark this user as an owner ***
                // Add any other required user fields here...
                // 'email_verified_at' => now(), // You might want to handle email verification later
            ]);

            Log::info('New owner user created successfully.', ['user_id' => $user->id, 'email' => $user->email]);

            // *** Optional: Log the user in immediately after registration ***
            // If you want the owner to be logged in right after signing up
            Auth::login($user);
            Log::info('New owner user logged in automatically after registration.', ['user_id' => $user->id]);

            // --- Redirect after successful registration ---
            // Redirect to the owner dashboard
            return redirect()->route('owner.dashboard')->with('success', 'Owner account created successfully! You are now logged in.');

            // If you don't auto-login, you might redirect to the login page:
            // return redirect()->route('login')->with('success', 'Owner account created successfully! Please log in.');

        } catch (\Exception $e) {
            Log::error('Failed to register new owner user: '.$e->getMessage(), ['exception' => $e]);

            // Redirect back to the registration form with an error message and old input
            return redirect()->route('owner.register')->withInput()->with('error', 'Failed to register owner account.');
        }
    }

    // You might add a logout method here later
    // public function logout(Request $request) { ... }
}
