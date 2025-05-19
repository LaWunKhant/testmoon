<?php

namespace App\Http\Controllers;

use App\Models\House;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; // Import JsonResponse

class HouseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $houses = House::all();

        return view('houses.index', compact('houses'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('houses.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'owner_id' => 'required|exists:users,id',
            // Add other validation rules as needed
        ]);

        try {
            $house = House::create($validated);

            return response()->json(['message' => 'House created successfully', 'house' => $house], 201); // Return JSON for API
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create house', 'error' => $e->getMessage()], 500); // Handle errors
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(House $house)
    {
        // $house is already resolved here, no need to find it
        return view('houses.show', compact('house'));
    }

    public function edit(House $house)
    {
        // $house is already resolved here, no need to find it
        return view('houses.edit', compact('house'));
    }

    public function update(Request $request, House $house)
    {
        // $house is already resolved here, no need to find it
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'owner_id' => 'required|exists:users,id',
            // Add other validation rules
        ]);

        $house->update($validated);

        return redirect()->route('houses.index')->with('success', 'House updated successfully');
    }

    public function destroy(House $house)
    {
        // $house is already resolved here, no need to find it
        $house->delete();

        return redirect()->route('houses.index')->with('success', 'House deleted successfully');
    }
}
