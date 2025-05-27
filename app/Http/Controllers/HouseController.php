<?php

namespace App\Http\Controllers;

use App\Mail\CustomTenantEmail;
use App\Models\House;
use App\Models\Tenant;
use Exception;
// Removed: use Illuminate\Http\UploadedFile; // Not needed if no file uploads
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator; // Add this import for clearer exception handling

class HouseController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    // --- Owner House Management Methods ---

    /**
     * Show the owner's dashboard with their houses and tenants.
     */
    public function ownerDashboard()
    {
        $ownerId = Auth::id();
        $ownerHouses = House::where('owner_id', $ownerId)->with('tenants')->get();

        return view('owner.dashboard', compact('ownerHouses'));
    }

    /**
     * Show the form for creating a new house for the owner.
     */
    public function createForOwner()
    {
        return view('owner.houses.create');
    }

    /**
     * Store a newly created house record for the owner.
     */
    public function storeForOwner(Request $request)
    {
        Log::info('Attempting to store new house for owner.', ['user_id' => (Auth::id() ?? 'NULL')]);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);
        if ($validator->fails()) {
            Log::warning('House creation validation failed.', ['user_id' => (Auth::id() ?? 'NULL'), 'errors' => $validator->errors()->all()]);

            return redirect()->back()->withErrors($validator)->withInput();
        }
        Log::info('House creation validation passed.');
        try {
            $houseData = $validator->validated();
            $houseData['owner_id'] = Auth::id();
            unset($houseData['photo']);
            $house = House::create($houseData);
            Log::info('House created successfully.', ['house_id' => $house->id, 'owner_id' => $house->owner_id]);
            if ($request->hasFile('photo') && $house) {
                $house->addMediaFromRequest('photo')->toMediaCollection('photos');
                Log::info('House photo uploaded and associated successfully via MediaLibrary.', ['house_id' => $house->id]);
            }

            return redirect()->route('owner.dashboard')->with('success', 'House added successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to store new house: '.$e->getMessage(), ['user_id' => (Auth::id() ?? 'NULL'), 'exception' => $e]);

            return redirect()->back()->withInput()->with('error', 'Failed to add house.');
        }
    }

    /**
     * Show the form for editing the specified house for the owner.
     */
    public function editForOwner(House $house)
    {
        if ($house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to edit house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

            return redirect()->route('owner.dashboard')->with('error', 'You do not own this house.');
        }
        Log::info('Showing edit form for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        return view('owner.houses.edit', compact('house'));
    }

    /**
     * Update the specified house record for the owner.
     */
    public function updateForOwner(Request $request, House $house)
    {
        Log::info('Attempting to update house for owner.', ['user_id' => Auth::id(), 'house_id' => $house->id]);
        if ($house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to update house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

            return redirect()->route('owner.dashboard')->with('error', 'You do not own this house.');
        }
        Log::info('Authorization check passed for house update.', ['user_id' => Auth::id(), 'house_id' => $house->id]);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'capacity' => ['required', 'integer', 'min:1'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ]);
        if ($validator->fails()) {
            Log::warning('House update validation failed.', ['user_id' => Auth::id(), 'house_id' => $house->id, 'errors' => $validator->errors()->all()]);

            return redirect()->route('owner.houses.edit', $house)->withErrors($validator)->withInput();
        }
        Log::info('House update validation passed.', ['user_id' => Auth::id(), 'house_id' => $house->id]);
        try {
            $houseData = $validator->validated();
            unset($houseData['photo']);
            $house->update($houseData);
            Log::info('House attributes updated successfully.', ['house_id' => $house->id]);
            if ($request->hasFile('photo')) {
                Log::info('New photo uploaded for house.', ['house_id' => $house->id]);
                $house->clearMediaCollection('photos');
                Log::info('Cleared existing photos collection for house.', ['house_id' => $house->id]);
                $house->addMediaFromRequest('photo')->toMediaCollection('photos');
                Log::info('New house photo uploaded and associated successfully via MediaLibrary.', ['house_id' => $house->id]);
            }

            return redirect()->route('owner.dashboard')->with('success', 'House updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update house: '.$e->getMessage(), ['user_id' => Auth::id(), 'house_id' => $house->id, 'exception' => $e]);

            return redirect()->route('owner.houses.edit', $house)->withInput()->with('error', 'Failed to update house.');
        }
    }

    /**
     * Remove the specified house record for the owner.
     */
    public function destroyForOwner(House $house)
    {
        Log::info('Attempting to delete house for owner.', ['user_id' => Auth::id(), 'house_id' => $house->id]);
        $house->load('tenants'); // Eager load tenants before auth check if needed for logic here
        if ($house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to delete house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

            return redirect()->route('owner.dashboard')->with('error', 'You do not own this house.');
        }
        Log::info('Authorization check passed for house deletion.', ['user_id' => Auth::id(), 'house_id' => $house->id]);
        try {
            $house->clearMediaCollection();
            Log::info('Cleared media collection for house during deletion.', ['house_id' => $house->id]);
            $house->delete();
            Log::info('House deleted successfully.', ['house_id' => $house->id]);

            return redirect()->route('owner.dashboard')->with('success', 'House deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete house: '.$e->getMessage(), ['user_id' => Auth::id(), 'house_id' => $house->id, 'exception' => $e]);

            return redirect()->route('owner.dashboard')->with('error', 'Failed to delete house.');
        }
    }

    // --- Owner Tenant Management Methods ---

    /**
     * Show the tenants associated with the specified house for the owner.
     */
    public function showTenants(House $house)
    {
        Log::info('Attempting to show tenants for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);
        if ($house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to view tenants for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

            return redirect()->route('owner.dashboard')->with('error', 'You do not own this house.');
        }
        Log::info('Authorization check passed for viewing tenants for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        return view('owner.houses.tenants', compact('house'));
    }

    /**
     * Show the form for creating a new tenant for the specified house for the owner.
     */
    public function createTenantForHouse(House $house)
    {
        Log::info('Attempting to show create tenant form for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);
        if ($house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to view create tenant form for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

            return redirect()->route('owner.dashboard')->with('error', 'You do not own this house.');
        }
        Log::info('Authorization check passed for viewing create tenant form for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

        return view('owner.houses.tenants.create', compact('house'));
    }

    /**
     * Store a newly created tenant record for the specified house for the owner.
     */
    public function storeTenantForHouse(Request $request, House $house)
    {
        Log::info('Attempting to store new tenant for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);
        if ($house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to store tenant for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);

            return redirect()->route('owner.dashboard')->with('error', 'You do not own this house.');
        }
        Log::info('Authorization check passed for storing tenant for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:tenants,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'rent' => ['required', 'numeric', 'min:0'],
        ]);
        if ($validator->fails()) {
            Log::warning('Tenant creation validation failed for house.', ['user_id' => Auth::id(), 'house_id' => $house->id, 'errors' => $validator->errors()->all()]);

            return redirect()->route('owner.houses.tenants.create', $house)->withErrors($validator)->withInput();
        }
        Log::info('Tenant creation validation passed for house.', ['user_id' => Auth::id(), 'house_id' => $house->id]);
        try {
            $tenantData = $validator->validated();
            $tenantData['house_id'] = $house->id;
            $tenant = Tenant::create($tenantData);
            Log::info('Tenant created successfully and linked to house.', ['tenant_id' => $tenant->id, 'house_id' => $house->id]);

            return redirect()->route('owner.houses.tenants.index', $house)->with('success', 'Tenant added successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to store new tenant for house: '.$e->getMessage(), ['user_id' => Auth::id(), 'house_id' => $house->id, 'exception' => $e]);

            return redirect()->route('owner.houses.tenants.create', $house)->withInput()->with('error', 'Failed to add tenant.');
        }
    }

    /**
     * Show the form for editing the specified tenant for the owner.
     */
    public function editTenant(Tenant $tenant)
    {
        Log::info('Attempting to show edit form for tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
        $tenant->load('house');
        if (! $tenant->house || $tenant->house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to edit tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
            $redirectRoute = $tenant->house ? route('owner.houses.tenants.index', $tenant->house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('error', 'You do not own this tenant or their house.');
        }
        Log::info('Authorization check passed for editing tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        return view('owner.tenants.edit', compact('tenant'));
    }

    /**
     * Update the specified tenant record for the owner.
     */
    public function updateTenant(Request $request, Tenant $tenant)
    {
        Log::info('Attempting to update tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
        $tenant->load('house');
        if (! $tenant->house || $tenant->house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to update tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
            $redirectRoute = $tenant->house ? route('owner.houses.tenants.index', $tenant->house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('error', 'You do not own this tenant or their house.');
        }
        Log::info('Authorization check passed for updating tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:tenants,email,'.$tenant->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'rent' => ['required', 'numeric', 'min:0'],
        ]);
        if ($validator->fails()) {
            Log::warning('Tenant update validation failed.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'errors' => $validator->errors()->all()]);

            return redirect()->route('owner.tenants.edit', $tenant)->withErrors($validator)->withInput();
        }
        Log::info('Tenant update validation passed.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
        try {
            $tenantData = $validator->validated();
            $tenant->update($tenantData);
            Log::info('Tenant updated successfully.', ['tenant_id' => $tenant->id]);

            return redirect()->route('owner.houses.tenants.index', $tenant->house)->with('success', 'Tenant updated successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to update tenant: '.$e->getMessage(), ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'exception' => $e]);

            return redirect()->route('owner.tenants.edit', $tenant)->withInput()->with('error', 'Failed to update tenant.');
        }
    }

    /**
     * Remove the specified tenant record for the owner.
     */
    public function destroyTenant(Tenant $tenant)
    {
        Log::info('Attempting to delete tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
        $tenant->load('house');
        if (! $tenant->house || $tenant->house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to delete tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
            $redirectRoute = $tenant->house ? route('owner.houses.tenants.index', $tenant->house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('error', 'You do not own this tenant or their house.');
        }
        Log::info('Authorization check passed for deleting tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
        try {
            $tenant->delete();
            Log::info('Tenant deleted successfully.', ['tenant_id' => $tenant->id]);
            $houseId = $tenant->house_id;
            $house = \App\Models\House::find($houseId);
            $redirectRoute = $house ? route('owner.houses.tenants.index', $house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('success', 'Tenant deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to delete tenant: '.$e->getMessage(), ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'exception' => $e]);
            $houseId = $tenant->house_id;
            $house = \App\Models\House::find($houseId);
            $redirectRoute = $house ? route('owner.houses.tenants.index', $house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('error', 'Failed to delete tenant.');
        }
    }

    /**
     * Show the form for composing an email to a specific tenant for the owner.
     */
    public function composeEmail(\App\Models\Tenant $tenant)
    {
        Log::info('Attempting to show compose email form for tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
        $tenant->load('house');
        if (! $tenant->house || $tenant->house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to view compose email form for tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
            $redirectRoute = $tenant->house ? route('owner.houses.tenants.index', $tenant->house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('error', 'You do not own this tenant or their house.');
        }
        Log::info('Authorization check passed for viewing compose email form for tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        return view('owner.tenants.compose-email', compact('tenant'));
    }

    /**
     * Handle the submission of the compose email form and send the email.
     */
    public function sendEmail(Request $request, Tenant $tenant)
    {
        Log::info('Attempting to send email to tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'tenant_email' => $tenant->email]);
        $tenant->load('house');
        if (! $tenant->house || $tenant->house->owner_id !== Auth::id()) {
            Log::warning('Unauthorized attempt to send email to tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);
            $redirectRoute = $tenant->house ? route('owner.houses.tenants.index', $tenant->house) : route('owner.dashboard');

            return redirect($redirectRoute)->with('error', 'You do not own this tenant or their house.');
        }
        Log::info('Authorization check passed for sending email to tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        $validator = Validator::make($request->all(), [
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'tenant_id' => ['required', 'exists:tenants,id'],
            // Removed: 'attachments.*' => ['nullable', 'file', 'max:5120'],
        ]);

        if ($validator->fails()) {
            Log::warning('Email composition validation failed.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'errors' => $validator->errors()->all()]);

            return redirect()->route('owner.tenants.compose-email', $tenant)->withErrors($validator)->withInput();
        }
        Log::info('Email composition validation passed.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

        try {
            // Removed all file upload handling logic
            // $uploadedFiles = [];
            // if ($request->hasFile('attachments')) { ... }

            if ($tenant->email) {
                // Modified Mailable instantiation - no $uploadedFiles parameter
                $emailMailable = new CustomTenantEmail($request->subject, $request->body, $tenant);
                Mail::to($tenant->email)->send($emailMailable);
                Log::info('Custom email sent successfully to tenant.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'recipient_email' => $tenant->email]);

                return redirect()->route('owner.houses.tenants.index', $tenant->house)->with('success', 'Email sent successfully!');
            } else {
                Log::warning('Cannot send email to tenant: Tenant has no email address.', ['user_id' => Auth::id(), 'tenant_id' => $tenant->id]);

                return redirect()->route('owner.houses.tenants.index', $tenant->house)->with('warning', 'Tenant does not have an email address.');
            }
        } catch (\Exception $e) {
            Log::error('Failed to send email to tenant: '.$e->getMessage(), ['user_id' => Auth::id(), 'tenant_id' => $tenant->id, 'exception' => $e]);

            return redirect()->route('owner.tenants.compose-email', $tenant)->withInput()->with('error', 'Failed to send email.');
        }
    }
}
