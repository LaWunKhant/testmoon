<?php

namespace App\Http\Controllers;

use App\Jobs\SendMaintenanceNotificationJob;
use App\Models\MaintenanceRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class MaintenanceRequestController extends Controller
{
    /**
     * Store a new maintenance request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Fetch all maintenance requests, ordered by creation date
        $maintenanceRequests = MaintenanceRequest::orderBy('created_at', 'desc')->get();

        // Return them as a JSON response
        return response()->json($maintenanceRequests);

        // If you were returning a view, it would look something like this:
        // return view('maintenance_requests.index', compact('maintenanceRequests'));
    }

    public function store(Request $request)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'house_id' => 'required|exists:houses,id',
            'description' => 'required|string',
            'scheduled_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Create the maintenance request
            $maintenanceRequest = MaintenanceRequest::create($validator->validated());

            // Dispatch the job to send the notification email
            dispatch(new SendMaintenanceNotificationJob($maintenanceRequest));

            return response()->json(['message' => 'Maintenance request created successfully. Notification sent!'], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create maintenance request: '.$e->getMessage());

            return response()->json(['message' => 'Failed to create maintenance request.'], 500);
        }
    }

    /**
     * Update the specified maintenance request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'scheduled_date' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Update the maintenance request
            $maintenanceRequest->update($validator->validated());

            // Dispatch the job to send the notification email
            dispatch(new SendMaintenanceNotificationJob($maintenanceRequest, true)); // Pass true to indicate it's an update

            return response()->json(['message' => 'Maintenance request updated successfully. Notification sent!'], 200);
        } catch (\Exception $e) {
            Log::error('Failed to update maintenance request: '.$e->getMessage());

            return response()->json(['message' => 'Failed to update maintenance request.'], 500);
        }
    }

    /**
     * Display the specified maintenance request.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(MaintenanceRequest $maintenanceRequest)
    {
        return response()->json($maintenanceRequest);
    }
}
