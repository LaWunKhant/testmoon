<?php

namespace App\Http\Controllers;

// --- Add necessary Use statements here at the top, correctly ---
// Use your application's base controller
use App\Models\House;
use Illuminate\Http\Request; // For the House model
use Illuminate\Support\Facades\Auth; // For authentication checks
use Illuminate\Support\Facades\Log; // For logging messages
use Illuminate\Support\Facades\Storage; // For file uploads (photo)
use Illuminate\Support\Facades\Validator; // For request validation

// For redirecting responses
// For potential JSON responses (optional, but good return type hint)
// For catching general exceptions

// --- End Use statements ---

class HouseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Apply the 'auth' middleware to this controller's methods
        // This ensures only authenticated users can access these routes.
        $this->middleware('auth');
    }

    /**
     * Show the form for creating a new house for the owner.
     * Protected by 'auth' middleware.
     *
     * @return \Illuminate\View\View
     */
    public function createForOwner()
    {
        // This method simply returns the view containing the form
        // The owner_id will be automatically set to the logged-in user's ID when storing the house
        return view('owner.houses.create'); // Return the name of your create house view file
    }

    /**
     * Store a newly created house record for the owner.
     * Protected by 'auth' middleware.
     * Handles form submission from owner.houses.create.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function storeForOwner(Request $request)
    {
        Log::info('Attempting to store new house for owner.', ['user_id' => (Auth::id() ?? 'NULL')]);

        // --- Validation ---
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'], // Validation for sharehouse capacity
            'photo' => ['nullable', 'image', 'max:2048'], // Validation for optional image upload (max 2MB)
        ]);

        if ($validator->fails()) {
            Log::warning('House creation validation failed.', ['errors' => $validator->errors()->all()]);

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        Log::info('House creation validation passed.');

        try {
            // --- Handle Photo Upload ---
            $photoPath = null;
            if ($request->hasFile('photo')) {
                // Store the uploaded photo in the 'public' disk (storage/app/public)
                // The 'public' disk is symlinked to public/storage
                $photoPath = $request->file('photo')->store('photos/houses', 'public'); // Store in storage/app/public/photos/houses
                Log::info('House photo uploaded successfully.', ['path' => $photoPath]);
            }

            // --- Prepare House Data ---
            $houseData = $validator->validated();

            // *** Logging for Session ID and Auth State ***
            // These logs will show us the state during the POST request handling
            Log::info('Checking session ID and authentication state before setting owner_id.');
            Log::info('Session ID from request: '.($request->session()->getId() ?? 'NULL'));
            Log::info('Auth::check() result: '.(Auth::check() ? 'True' : 'False'));
            Log::info('Auth::id() result: '.(Auth::id() ?? 'NULL'));
            // *** End Auth State Logging ***

            $houseData['owner_id'] = Auth::id(); // Set the owner_id to the logged-in user's ID

            if ($photoPath) {
                $houseData['photo_path'] = $photoPath; // Assuming you have a 'photo_path' column in your houses table
            }

            // --- Create the House Record ---
            $house = House::create($houseData);

            Log::info('House created successfully.', ['house_id' => $house->id, 'owner_id' => $house->owner_id]);

            // --- Redirect after successful creation ---
            return redirect()->route('owner.dashboard')->with('success', 'House added successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to store new house: '.$e->getMessage(), ['user_id' => (Auth::id() ?? 'NULL'), 'exception' => $e]); // Log user ID even on failure

            // Handle potential file deletion if saving to DB failed after upload
            if ($photoPath && Storage::disk('public')->exists($photoPath)) {
                Storage::disk('public')->delete($photoPath);
                Log::warning('Uploaded house photo deleted due to database error.', ['path' => $photoPath]);
            }

            // Redirect back to the form with an error message and old input
            return redirect()->back()->withInput()->with('error', 'Failed to add house.');
        }
    }

    // Keep your existing methods here (e.g., index, show, edit, update, destroy)
    // that are not owner-specific house management
}
