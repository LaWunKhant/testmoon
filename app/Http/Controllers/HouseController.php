<?php

namespace App\Http\Controllers;

// --- Add necessary Use statements here at the top, correctly ---
// Use your application's base controller
use App\Models\House;
use Illuminate\Http\Request; // For the House model
use Illuminate\Support\Facades\Auth; // For authentication checks
use Illuminate\Support\Facades\Log; // For logging messages
// For file uploads (photo)
use Illuminate\Support\Facades\Validator; // For request validation

// *** For redirecting responses (add this) ***
// *** For potential JSON responses (add this) ***
// *** For catching general exceptions (add this) ***

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
        // Ensure necessary Use statements are at the top:
        // use Illuminate\Support\Facades\Validator;
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'photo' => ['nullable', 'image', 'max:2048'], // Validation for optional image upload remains
        ]);

        if ($validator->fails()) {
            Log::warning('House creation validation failed.', ['errors' => $validator->errors()->all()]);

            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        Log::info('House creation validation passed.');

        try {
            // --- Create the House Record First ---
            $houseData = $validator->validated();
            $houseData['owner_id'] = Auth::id();
            // *** Ensure 'photo' is removed from $houseData as MediaLibrary handles it ***
            unset($houseData['photo']);

            $house = \App\Models\House::create($houseData); // Ensure App\Models\House is used or imported

            Log::info('House created successfully.', ['house_id' => $house->id, 'owner_id' => $house->owner_id]);

            // --- Handle Photo Upload Using Spatie MediaLibrary ---
            // Check if a photo was uploaded via the 'photo' input field
            if ($request->hasFile('photo') && $house) { // Ensure house was created successfully
                // Use the addMediaFromRequest method to handle the upload and associate with the house
                $house->addMediaFromRequest('photo')
                    ->toMediaCollection('photos'); // 'photos' is the default collection name, or define your own

                Log::info('House photo uploaded and associated successfully via MediaLibrary.', ['house_id' => $house->id]);
            }

            // --- Redirect after successful creation ---
            return redirect()->route('owner.dashboard')->with('success', 'House added successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to store new house: '.$e->getMessage(), ['user_id' => (Auth::id() ?? 'NULL'), 'exception' => $e]);

            // Spatie MediaLibrary handles file deletion if association fails after upload,
            // but if the House model wasn't created, the file wouldn't be associated anyway.
            // Manual cleanup for partially uploaded files before House creation is complex
            // and often not needed with addMediaFromRequest unless you save the file first.
            // The try/catch around the House::create and addMediaFromRequest will handle
            // errors during that process.

            return redirect()->back()->withInput()->with('error', 'Failed to add house.');
        }
    }

    public function editForOwner(House $house)
    {
        // Ensure the logged-in owner owns this house before allowing editing
        if ($house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to edit house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

            // Redirect back to the dashboard with an error message if owner doesn't own the house
            return redirect()->route('owner.dashboard')->with('error', 'You do not own this house.');
        }

        Log::info('Showing edit form for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        // Return the view, passing the house model to it
        return view('owner.houses.edit', compact('house')); // *** Return the name of your edit house view file ***
    }

    public function updateForOwner(Request $request, House $house)
    {
        Log::info('Attempting to update house for owner.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        // --- Authorization Check (Same as editForOwner) ---
        if ($house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to update house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

            // Redirect back to the dashboard with an error message if owner doesn't own the house
            return redirect()->route('owner.dashboard')->with('error', 'You do not own this house.');
        }
        Log::info('Authorization check passed for house update.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        // --- Validation (Similar to store, but without 'required' for fields that might not be changed) ---
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'], // Name is still required
            'address' => ['required', 'string', 'max:255'], // Address is still required
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'], // Price is still required
            'capacity' => ['required', 'integer', 'min:1'], // Capacity is still required
            'photo' => ['nullable', 'image', 'max:2048'], // Validation for optional NEW image upload
        ]);

        if ($validator->fails()) {
            Log::warning('House update validation failed.', ['user_id' => Auth::id(), 'house_id' => $house->id, 'errors' => $validator->errors()->all()]);

            // Redirect back to the edit form with validation errors and old input
            // Pass the house model to the redirect route so the URL is correctly generated
            return redirect()->route('owner.houses.edit', $house)
                ->withErrors($validator)
                ->withInput();
        }
        Log::info('House update validation passed.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        try {
            // --- Prepare House Data for Update ---
            // Use the validated data. owner_id is not updated via the form.
            $houseData = $validator->validated();
            // *** Remove 'photo' from $houseData as MediaLibrary handles it ***
            unset($houseData['photo']);

            // --- Update the House Record ---
            $house->update($houseData); // Update the house attributes

            Log::info('House attributes updated successfully.', ['house_id' => $house->id]);

            // --- Handle Photo Upload/Update Using Spatie MediaLibrary ---
            // Check if a NEW photo was uploaded via the 'photo' input field
            if ($request->hasFile('photo')) {
                Log::info('New photo uploaded for house.', ['house_id' => $house->id]);

                // *** Clear the existing media in the 'photos' collection (optional, but common for replacement) ***
                // This deletes the old photo(s) associated with this house in the 'photos' collection.
                // Only do this if you want the new photo to REPLACE the old one.
                $house->clearMediaCollection('photos');
                Log::info('Cleared existing photos collection for house.', ['house_id' => $house->id]);

                // Use the addMediaFromRequest method to handle the upload and associate with the house
                $house->addMediaFromRequest('photo')
                    ->toMediaCollection('photos'); // Add the new photo to the 'photos' collection

                Log::info('New house photo uploaded and associated successfully via MediaLibrary.', ['house_id' => $house->id]);

            }
            // Note: If no new photo is uploaded, the existing photo (if any) remains unchanged.
            // You might add a checkbox in the form to allow deleting the photo without uploading a new one.

            // --- Redirect after successful update ---
            // Redirect back to the owner dashboard with a success message
            return redirect()->route('owner.dashboard')->with('success', 'House updated successfully!');

            // Alternatively, redirect back to the edit form with a success message:
            // return redirect()->route('owner.houses.edit', $house)->with('success', 'House updated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to update house: '.$e->getMessage(), ['user_id' => Auth::id(), 'house_id' => $house->id, 'exception' => $e]);

            // Spatie MediaLibrary handles rollback of media association if saving the media record fails.
            // Manual cleanup for the uploaded file *before* association might be complex, but addMediaFromRequest
            // handles temp file creation internally.

            // Redirect back to the edit form with an error message and old input
            return redirect()->route('owner.houses.edit', $house)->withInput()->with('error', 'Failed to update house.');
        }
    }

    public function destroyForOwner(House $house)
    {
        Log::info('Attempting to delete house for owner.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        // --- Authorization Check (Same as edit/update) ---
        if ($house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to delete house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

            // Redirect back to the dashboard with an error message if owner doesn't own the house
            return redirect()->route('owner.dashboard')->with('error', 'You do not own this house.');
        }
        Log::info('Authorization check passed for house deletion.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        try {
            // --- Delete Associated Media using Spatie MediaLibrary ---
            // This deletes all media files associated with this house model (in all collections)
            $house->clearMediaCollection(); // Clear all media collections for this house

            Log::info('Cleared media collection for house during deletion.', ['house_id' => $house->id]);

            // --- Delete the House Record ---
            $house->delete(); // Delete the house record from the database

            Log::info('House deleted successfully.', ['house_id' => $house->id]);

            // --- Redirect after successful deletion ---
            // Redirect back to the owner dashboard with a success message
            return redirect()->route('owner.dashboard')->with('success', 'House deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to delete house: '.$e->getMessage(), ['user_id' => Auth::id(), 'house_id' => $house->id, 'exception' => $e]);

            // Redirect back to the dashboard with an error message
            return redirect()->route('owner.dashboard')->with('error', 'Failed to delete house.');
        }
    }

    // Keep your existing methods here (e.g., index, show, edit, update, destroy)
    // that are not owner-specific house management
}
