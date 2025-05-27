<?php

namespace App\Http\Controllers;

// --- Add necessary Use statements here at the top, correctly ---
// Use your application's base controller

use App\Mail\CustomTenantEmail;
use App\Models\House;
use App\Models\Tenant;
use Illuminate\Http\Request; // For the House model
use Illuminate\Support\Facades\Auth; // For authentication checks
use Illuminate\Support\Facades\Log; // For logging messages
use Illuminate\Support\Facades\Mail;
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

    public function showTenants(House $house)
    {
        Log::info('Attempting to show tenants for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        // --- Authorization Check (Owner must own this house) ---
        if ($house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to view tenants for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

            return redirect()->route('owner.dashboard')->with('error', 'You do not own this house.');
        }
        Log::info('Authorization check passed for viewing tenants for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        // The $house model is already loaded by Route Model Binding.
        // The tenants are already accessible via the relationship: $house->tenants
        // If you need to eager load relationships on tenants (like their rent payments), do it here:
        // $house->load('tenants.rentPayments'); // Eager load rent payments for each tenant

        // Return the view, passing the house model (with eager-loaded tenants if done) to it
        return view('owner.houses.tenants', compact('house')); // Return the name of your show tenants view file
    }

    public function createTenantForHouse(House $house)
    {
        Log::info('Attempting to show create tenant form for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        // --- Authorization Check (Owner must own this house) ---
        if ($house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to view create tenant form for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

            // Redirect back to the dashboard with an error message if owner doesn't own the house
            return redirect()->route('owner.dashboard')->with('error', 'You do not own this house.');
        }
        Log::info('Authorization check passed for viewing create tenant form for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        // Return the view, passing the house model to it
        // The view will know which house the new tenant is being added to
        return view('owner.houses.tenants.create', compact('house')); // *** Return the name of your create tenant view file ***
    }

    public function storeTenantForHouse(Request $request, House $house)
    {
        Log::info('Attempting to store new tenant for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        // --- Authorization Check (Owner must own this house) ---
        // This check is important to prevent someone from adding a tenant to a house they don't own
        if ($house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to store tenant for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

            // Redirect back to the dashboard with an error message if owner doesn't own the house
            return redirect()->route('owner.dashboard')->with('error', 'You do not own this house.');
        }
        Log::info('Authorization check passed for storing tenant for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        // --- Validation ---
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:tenants,email'], // Ensure email is unique in tenants table
            'phone' => ['nullable', 'string', 'max:20'], // Adjust max length as needed for phone numbers
            'rent' => ['required', 'numeric', 'min:0'],
            // We don't need to validate house_id here, as we get it from the route/Route Model Binding
        ]);

        if ($validator->fails()) {
            Log::warning('Tenant creation validation failed for house.', ['user_id' => Auth::id(), 'house_id' => $house->id, 'errors' => $validator->errors()->all()]);

            // Redirect back to the create tenant form with validation errors and old input
            // Pass the house model to the redirect route so the URL is correctly generated
            return redirect()->route('owner.houses.tenants.create', $house)
                ->withErrors($validator)
                ->withInput();
        }
        Log::info('Tenant creation validation passed for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        try {
            // --- Create the Tenant Record and Link to House ---
            $tenantData = $validator->validated();
            // *** Set the house_id for the new tenant to the ID of the current house ***
            $tenantData['house_id'] = $house->id;

            // Ensure necessary Use statements are at the top:
            // use App\Models\Tenant;
            $tenant = Tenant::create($tenantData); // Create the new tenant record in the database

            Log::info('Tenant created successfully and linked to house.', ['tenant_id' => $tenant->id, 'house_id' => $house->id]);

            // --- Redirect after successful creation ---
            // Redirect back to the tenant list page for this house with a success message
            return redirect()->route('owner.houses.tenants.index', $house)->with('success', 'Tenant added successfully!');

            // Alternatively, redirect back to the create tenant form to add another:
            // return redirect()->route('owner.houses.tenants.create', $house)->with('success', 'Tenant added successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to store new tenant for house: '.$e->getMessage(), ['user_id' => Auth::id(), 'house_id' => $house->id, 'exception' => $e]);

            // Redirect back to the create tenant form with an error message and old input
            return redirect()->route('owner.houses.tenants.create', $house)->withInput()->with('error', 'Failed to add tenant.');
        }
    }

    public function editTenant(Tenant $tenant) // Use full namespace or ensure Tenant model is imported
    {
        Log::info('Attempting to show edit form for tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        // --- Authorization Check (Owner must own the house this tenant is linked to) ---
        // To perform this check, we need access to the house relationship on the tenant.
        // Ensure the 'house' relationship is defined in your Tenant model: public function house() { return $this->belongsTo(House::class); }
        // And ensure the house is owned by the logged-in user.
        if (! $tenant->house || $tenant->house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to edit tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
            // Redirect back to the dashboard or the house tenants list with an error
            // Redirecting to the specific house's tenant list is better if the tenant is linked to a house.
            // Need the house ID for the redirect route owner.houses.tenants.index
            $redirectRoute = $tenant->house ? route('owner.houses.tenants.index', $tenant->house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('error', 'You do not own this tenant or their house.');
        }
        Log::info('Authorization check passed for editing tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        // Return the view, passing the tenant model to it
        return view('owner.tenants.edit', compact('tenant')); // *** Return the name of your edit tenant view file ***
    }

    public function updateTenant(Request $request, Tenant $tenant) // Ensure Tenant is imported or use full namespace
    {
        Log::info('Attempting to update tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        // --- Authorization Check (Owner must own the house this tenant is linked to) ---
        // Ensure the 'house' relationship is defined in your Tenant model
        if (! $tenant->house || $tenant->house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to update tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
            // Redirect back to the dashboard or the house tenants list with an error
            $redirectRoute = $tenant->house ? route('owner.houses.tenants.index', $tenant->house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('error', 'You do not own this tenant or their house.');
        }
        Log::info('Authorization check passed for updating tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        // --- Validation (Similar to store, but email unique rule needs to ignore the current tenant) ---
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:tenants,email,'.$tenant->id], // Unique email, but ignore the current tenant's email
            'phone' => ['nullable', 'string', 'max:20'],
            'rent' => ['required', 'numeric', 'min:0'],
            // house_id is not updated via this form
        ]);

        if ($validator->fails()) {
            Log::warning('Tenant update validation failed.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'errors' => $validator->errors()->all()]);

            // Redirect back to the edit tenant form with validation errors and old input
            // Pass the tenant model to the redirect route so the URL is correctly generated
            return redirect()->route('owner.tenants.edit', $tenant)
                ->withErrors($validator)
                ->withInput();
        }
        Log::info('Tenant update validation passed.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        try {
            // --- Prepare Tenant Data for Update ---
            // Use the validated data. house_id is not updated via the form.
            $tenantData = $validator->validated();

            // --- Update the Tenant Record ---
            $tenant->update($tenantData); // Update the tenant attributes

            Log::info('Tenant updated successfully.', ['tenant_id' => $tenant->id]);

            // --- Redirect after successful update ---
            // Redirect back to the tenant list page for the house this tenant is linked to
            // Ensure the 'house' relationship is loaded to get the house ID for the redirect route
            return redirect()->route('owner.houses.tenants.index', $tenant->house)->with('success', 'Tenant updated successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to update tenant: '.$e->getMessage(), ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'exception' => $e]);

            // Redirect back to the edit tenant form with an error message and old input
            return redirect()->route('owner.tenants.edit', $tenant)->withInput()->with('error', 'Failed to update tenant.');
        }
    }

    public function destroyTenant(Tenant $tenant) // Ensure Tenant is imported or use full namespace
    {
        Log::info('Attempting to delete tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        // --- Authorization Check (Owner must own the house this tenant is linked to) ---
        // Ensure the 'house' relationship is defined in your Tenant model
        // Eager load the house relationship before the check to avoid N+1 query if not already loaded
        $tenant->load('house');

        if (! $tenant->house || $tenant->house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to delete tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
            // Redirect back to the dashboard or the house tenants list with an error
            $redirectRoute = $tenant->house ? route('owner.houses.tenants.index', $tenant->house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('error', 'You do not own this tenant or their house.');
        }
        Log::info('Authorization check passed for deleting tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        try {
            // --- Delete the Tenant Record ---
            // Before deleting the tenant, consider cascading deletes for related data (e.g., rent payments, maintenance requests linked to this tenant).
            // You might need to configure cascade deletes in your database migrations for these relationships,
            // or manually delete related records here before deleting the tenant.
            // For simplicity now, we'll just delete the tenant record.
            $tenant->delete(); // Delete the tenant record from the database

            Log::info('Tenant deleted successfully.', ['tenant_id' => $tenant->id]);

            // --- Redirect after successful deletion ---
            // Redirect back to the tenant list page for the house the tenant *was* linked to
            // We need the house ID *before* deleting the tenant record
            $houseId = $tenant->house_id; // Get the house_id before deleting the tenant

            // Ensure the house still exists before redirecting to its tenant list
            $house = \App\Models\House::find($houseId);
            $redirectRoute = $house ? route('owner.houses.tenants.index', $house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('success', 'Tenant deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Failed to delete tenant: '.$e->getMessage(), ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'exception' => $e]);

            // Redirect back to the tenant list page or dashboard with an error
            // Need the house ID if redirecting back to the tenant list
            $houseId = $tenant->house_id;
            $house = \App\Models\House::find($houseId);
            $redirectRoute = $house ? route('owner.houses.tenants.index', $house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('error', 'Failed to delete tenant.');
        }
    }

    public function composeEmail(\App\Models\Tenant $tenant) // Use full namespace or ensure Tenant model is imported
    {
        Log::info('Attempting to show compose email form for tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        // --- Authorization Check (Owner must own the house this tenant is linked to) ---
        // Ensure the 'house' relationship is defined in your Tenant model
        $tenant->load('house'); // Eager load the house relationship for the check

        if (! $tenant->house || $tenant->house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to view compose email form for tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
            // Redirect back to the dashboard or the house tenants list with an error
            $redirectRoute = $tenant->house ? route('owner.houses.tenants.index', $tenant->house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('error', 'You do not own this tenant or their house.');
        }
        Log::info('Authorization check passed for viewing compose email form for tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        // Return the view, passing the tenant model to it
        return view('owner.tenants.compose-email', compact('tenant')); // *** Return the name of your compose email view file ***
    }

    public function sendEmail(Request $request, Tenant $tenant) // Ensure Tenant is imported or use full namespace
    {
        Log::info('Attempting to send email to tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'tenant_email' => $tenant->email]);

        // --- Authorization Check (Owner must own the house this tenant is linked to) ---
        // Ensure the 'house' relationship is defined in your Tenant model
        $tenant->load('house'); // Eager load the house relationship for the check

        if (! $tenant->house || $tenant->house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to send email to tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
            // Redirect back with an error
            $redirectRoute = $tenant->house ? route('owner.houses.tenants.index', $tenant->house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('error', 'You do not own this tenant or their house.');
        }
        Log::info('Authorization check passed for sending email to tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        // --- Validation for Email Subject, Body, and Attachments ---
        $validator = Validator::make($request->all(), [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'tenant_id' => ['required', 'exists:tenants,id'],
            'attachments.*' => ['nullable', 'file', 'max:5120'], // Validation for attachments (each file max 5MB)
            // 'attachments' => ['max:10'], // Optional: Max number of files (if multiple is allowed)
        ]);

        if ($validator->fails()) {
            Log::warning('Email composition validation failed.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'errors' => $validator->errors()->all()]);

            // Redirect back to the compose email form with validation errors and old input
            // Pass the tenant model to the redirect route so the URL is correctly generated
            return redirect()->route('owner.tenants.compose-email', $tenant)
                ->withErrors($validator)
                ->withInput();
        }
        Log::info('Email composition validation passed.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        try {
            // *** Get Uploaded Files from the Request ***
            $uploadedFiles = []; // Initialize an empty array for uploaded files
            // Check if any files were uploaded with the name 'attachments' (from the form file input)
            if ($request->hasFile('attachments')) {
                // Get the array of UploadedFile objects for the 'attachments' input
                // Only include files that were successfully uploaded (not null)
                $uploadedFiles = array_filter((array) $request->file('attachments'));
                Log::info('Received uploaded files for email.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'file_count' => count($uploadedFiles)]);
            }
            // *** End Get Uploaded Files ***

            // --- Send the Email using the Mailable ---
            // Ensure the tenant has an email address
            if ($tenant->email) {
                // Instantiate your custom email Mailable, passing subject, body, tenant, ***and the uploaded files***
                $emailMailable = new CustomTenantEmail($request->subject, $request->body, $tenant, $uploadedFiles); // *** Pass $uploadedFiles to the Mailable constructor ***

                // Dispatch the Mailable to the tenant's email address
                // Use Mail::to() for the recipient
                Mail::to($tenant->email)->send($emailMailable); // Use send() for immediate sending, or queue() for background

                Log::info('Custom email sent successfully to tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'recipient_email' => $tenant->email]);

                // --- Redirect after successful sending ---
                return redirect()->route('owner.houses.tenants.index', $tenant->house)->with('success', 'Email sent successfully!');

            } else {
                Log::warning('Cannot send email to tenant: Tenant has no email address.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

                // Redirect back with a warning message
                return redirect()->route('owner.houses.tenants.index', $tenant->house)->with('warning', 'Tenant does not have an email address.');
            }

        } catch (\Exception $e) {
            Log::error('Failed to send email to tenant: '.$e->getMessage(), ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'exception' => $e]);

            // Redirect back to the compose email form with an error message and old input
            return redirect()->route('owner.tenants.compose-email', $tenant)->withInput()->with('error', 'Failed to send email.');
        }
    }

    // Keep your existing methods here (e.g., index, show, edit, update, destroy)
    // that are not owner-specific house management
}
