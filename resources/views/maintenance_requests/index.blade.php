<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Requests</title>
    <style>
        /* Add some basic CSS for the table */
        table {
            border-collapse: collapse;
            width: 80%; /* Adjust width as needed */
            margin: 20px auto; /* Center the table */
            font-family: sans-serif;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        /* Add some minimal styling for readability */
        pre {
            white-space: pre-wrap; /* Wrap long descriptions */
        }
    </style>
</head>
<body>

    <h1>Maintenance Requests</h1>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>House ID</th>
                <th>Tenant ID</th>
                <th>Description</th>
                <th>Status</th>
                <th>Scheduled Date</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Actions</th> <!-- Column for actions -->
            </tr>
        </thead>
        <tbody>
            {{-- Loop through the maintenance requests --}}
            @foreach ($maintenanceRequests as $request)
                <tr>
                    <td>{{ $request->id }}</td>
                    <td>{{ $request->house_id }}</td>
                    <td>{{ $request->tenant_id }}</td>
                    {{-- You might want to limit the description length or format it --}}
                    <td><pre>{{ $request->description }}</pre></td>
                    <td>{{ $request->status }}</td>
                    {{-- Format dates nicely --}}
                    <td>{{ $request->scheduled_date ? \Carbon\Carbon::parse($request->scheduled_date)->format('Y-m-d') : 'N/A' }}</td>
                    <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $request->updated_at->format('Y-m-d H:i') }}</td>
                    <td>
                        {{-- Add links to show and edit pages --}}
                        <a href="{{ route('maintenance_requests.show', $request->id) }}">View</a> |
                        <a href="{{ route('maintenance_requests.edit', $request->id) }}">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>