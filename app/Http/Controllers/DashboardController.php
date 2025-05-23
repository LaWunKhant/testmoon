<?php

namespace App\Http\Controllers;

use App\Models\House; // Import Auth Facade
use Illuminate\Support\Facades\Auth; // Import House model

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Apply the 'auth' middleware to this controller's methods
        $this->middleware('auth');
    }

    /**
     * Show the owner's dashboard with their houses and tenants.
     *
     * @return \Illuminate\View\View
     */
    public function ownerDashboard()
    {
        // Get the currently authenticated user's ID
        $ownerId = Auth::id();

        // Fetch houses belonging to the authenticated owner, and eager load their tenants
        $ownerHouses = House::where('owner_id', $ownerId)
            ->with('tenants') // Eager load the tenants relationship
            ->get();

        // Return the view, passing the owner's houses data to it
        return view('owner.dashboard', compact('ownerHouses'));
    }

    // You might have other dashboard methods here
}
