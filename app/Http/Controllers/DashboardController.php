<?php

namespace App\Http\Controllers;

use App\Models\House;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // *** Import Log Facade ***

class DashboardController extends Controller
{
    // Keep the constructor with auth middleware...

    /**
     * Show the owner's dashboard with their houses and tenants.
     *
     * @return \Illuminate\View\View
     */
    public function ownerDashboard()
    {
        $ownerId = Auth::id();

        Log::info("Fetching houses for owner ID: {$ownerId}"); // Log owner ID

        // Fetch houses belonging to the authenticated owner, and eager load their tenants
        $ownerHouses = House::where('owner_id', $ownerId)
            ->with('tenants') // Eager load the tenants relationship
            ->get();

        Log::info('Found '.$ownerHouses->count()." houses for owner ID: {$ownerId}"); // Log number of houses found

        // *** Add logging to show house IDs and linked tenant IDs ***
        if ($ownerHouses->isEmpty()) {
            Log::info("No houses found for owner ID: {$ownerId}");
        } else {
            Log::info('Houses fetched and their linked tenants:');
            foreach ($ownerHouses as $house) {
                Log::info("  - House ID: {$house->id}, Address: {$house->address}");
                if ($house->tenants->isEmpty()) {
                    Log::info("    No tenants linked to House ID: {$house->id}");
                } else {
                    Log::info('    Linked tenants (IDs):');
                    foreach ($house->tenants as $tenant) {
                        Log::info("      - Tenant ID: {$tenant->id}, Name: {$tenant->name}");
                    }
                }
            }
        }
        // *** End logging block ***

        return view('owner.dashboard', compact('ownerHouses'));
    }

    // Keep other dashboard methods...
}
