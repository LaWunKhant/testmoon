<?php

namespace App\Http\Controllers\Auth;

// Use your application's base controller (recommended)
use App\Http\Controllers\Controller; // Ensure this is imported
use App\Models\Tenant;
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

    public function showTenantLoginForm()
    {
        // This method simply returns the view containing the tenant login form
        return view('auth.tenant-login'); // *** Return the name of your tenant login view file ***
    }

    public function showTenantRegistrationForm()
    {
        // This method simply returns the view containing the tenant registration form
        return view('auth.tenant-register'); // *** Return the name of your tenant registration view file ***
    }

    public function registerTenant(Request $request)
    {
        Log::info('Attempting to register new tenant user.');

        // --- Validation for Registration Data ---
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            // Ensure email is unique in the tenants table
            'email' => ['required', 'string', 'email', 'max:255', 'unique:tenants,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'], // 'confirmed' requires password_confirmation field
            // Add other required tenant fields here if they should be part of registration
            'phone' => ['nullable', 'string', 'max:20'],
            // 'rent' => ['required', 'numeric', 'min:0'], // If rent is set during sign up
            // house_id is likely NOT set during sign up, but linked later by an owner
        ]);

        if ($validator->fails()) {
            Log::warning('Tenant registration validation failed.', ['errors' => $validator->errors()->all()]);

            // Redirect back to the registration form with validation errors and old input
            return redirect()->route('tenant.register') // Redirect back to the tenant registration form
                ->withErrors($validator)
                ->withInput();
        }
        Log::info('Tenant registration validation passed.');

        try {
            // --- Create the New Tenant Record ---
            // Ensure App\Models\Tenant is imported at the top of the file
            // Ensure Illuminate\Support\Facades\Hash is imported at the top
            $tenant = Tenant::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password), // *** Hash the password before saving to tenants table ***
                // Add any other required tenant fields here from validation
                'phone' => $request->phone, // If phone was in validation
                // 'rent' => $request->rent, // If rent was in validation
                'house_id' => null, // Tenant likely not linked to a house immediately upon sign up
            ]);

            Log::info('New tenant user created successfully.', ['tenant_id' => $tenant->id, 'email' => $tenant->email]);

            // *** Optional: Log the tenant in immediately after registration ***
            // If you want the tenant to be logged in right after signing up
            // Note: This requires configuring a tenant authentication guard. We'll do that next.
            // Auth::guard('tenant')->login($tenant); // Assuming 'tenant' guard is configured

            // --- Redirect after successful registration ---
            // Redirect to the tenant login page or a tenant dashboard (which you'll build later)
            return redirect()->route('tenant.login')->with('success', 'Tenant account created successfully! Please log in.');

            // If you auto-login, redirect to a tenant dashboard route:
            // return redirect()->route('tenant.dashboard'); // Ensure this route exists later

        } catch (\Exception $e) {
            Log::error('Failed to register new tenant user: '.$e->getMessage(), ['exception' => $e]);

            // Redirect back to the registration form with an error message and old input
            return redirect()->route('tenant.register')->withInput()->with('error', 'Failed to register tenant account.');
        }
    }

    public function loginTenant(Request $request)
    {
        // Validate the login request data (similar to owner login)
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // *** Attempt to log the tenant in using the 'tenant' guard ***
        // Use Auth::guard('tenant')->attempt()
        if (Auth::guard('tenant')->attempt($credentials, $request->filled('remember'))) { // $request->filled('remember') for remember me (if added later)
            // Authentication successful...

            // Regenerate the session for the 'tenant' guard
            $request->session()->regenerate();

            Log::info('Tenant logged in successfully.', ['tenant_id' => Auth::guard('tenant')->id(), 'email' => $request->email]);

            // Redirect the tenant to their intended location or a default tenant dashboard
            // Ensure the tenant dashboard route exists later
            return redirect()->intended(route('tenant.dashboard')); // Redirect to tenant dashboard

        }

        // Authentication failed...
        Log::warning('Tenant login failed.', ['email' => $request->email, 'ip_address' => $request->ip()]);

        // Redirect back to the tenant login form with an error message and old input
        // Use the tenant login route name
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // You might add a logout method here later
    // public function logout(Request $request) { ... }
}
