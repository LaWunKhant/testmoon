<!DOCTYPE html>
<html>
<head>
    <title>Tenants for {{ $house->address ?? 'N/A' }}</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .house-details { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .house-details h1 { margin-bottom: 10px; }
        .house-details p { margin: 5px 0; }
        .tenants-list { margin-top: 20px; }
        .tenants-list h2 { margin-bottom: 15px; }
        .tenant-item { border: 1px solid #ccc; padding: 15px; margin-bottom: 15px; border-radius: 5px; }
        .tenant-item h3 { margin-top: 0; margin-bottom: 10px; color: #333; }
        .tenant-item p { margin: 5px 0; }

        /* --- Simplified Button Styles (Copy from dashboard.blade.php) --- */
        .btn {
            display: inline-block;
            padding: 8px 12px;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            border: none;
            margin-right: 5px; /* Space between buttons */
        }

        /* Specific Button Colors */
        .btn-primary { background-color: #007bff; }
        .btn-success { background-color: #28a745; }
        .btn-warning { background-color: #ffc107; color: #212529; }
        .btn-danger { background-color: #dc3545; color: white; }
        .btn-secondary { background-color: #6c757d; color: white; }


        .btn:hover { opacity: 0.9; }

        /* --- End Simplified Button Styles --- */


        /* Add back button style (Copy from dashboard.blade.php) */
         .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #6c757d; /* Gray color */
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
         .back-button:hover {
            background-color: #5a6268; /* Darker gray on hover */
        }

         /* Styles for tenant action links/buttons */
         .action-links a, .action-links button { /* Apply styles to both links and buttons */
            /* Removed margin-right: 10px; from here */
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 3px;
            display: inline-block;
            /* Removed specific margin left here, use btn margin-right instead */
        }

        /* Specific action button colors (Copy from dashboard.blade.php) */
        .edit-link { background-color: #ffc107; color: #212529; }
        .delete-link { background-color: #dc3545; color: white; }


    </style>
</head>
<body>

    <div class="container">
         {{-- Back button to owner dashboard --}}
         <a href="{{ route('owner.dashboard') }}" class="back-button" style="margin-bottom: 20px;">&larr; Back to Dashboard</a>

        <div class="house-details">
            <h1>Tenants for House: {{ $house->address ?? 'N/A' }}</h1>
            <p><strong>Name:</strong> {{ $house->name ?? 'N/A' }}</p>
            <p><strong>Price:</strong> ${{ number_format($house->price ?? 0, 2) }}</p>
            <p><strong>Description:</strong> {{ $house->description ?? 'N/A' }}</p>
            <p><strong>Capacity:</strong> {{ $house->capacity ?? 'N/A' }} tenants</p>
             {{-- Add Photo display here later if you want --}}
        </div>


        <div class="tenants-list">
            <h2>Tenant List</h2>

            @if ($house->tenants->isEmpty())
                <p>No tenants currently linked to this house.</p>
            @else
                @foreach ($house->tenants as $tenant)
                    <div class="tenant-item">
                        <h3>{{ $tenant->name ?? 'N/A' }}</h3>
                        <p><strong>Email:</strong> {{ $tenant->email ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $tenant->phone ?? 'N/A' }}</p>
                        <p><strong>Monthly Rent:</strong> ${{ number_format($tenant->rent ?? 0, 2) }}</p>
                        {{-- Add other tenant details here (e.g., rental period, payment summaries) --}}

                        {{-- *** Add Action Links/Buttons for Tenant (Edit, Delete, Mail) Here *** --}}
                        {{-- Placing these inside the tenant-item div --}}
                        <div class="action-links">
                            {{-- Edit Tenant Link --}}
                            {{-- Use the named route 'owner.tenants.edit' and pass the $tenant model --}}
                            <a href="{{ route('owner.tenants.edit', $tenant) }}" class="btn btn-warning">Edit Tenant</a> {{-- Using btn classes --}}

                            {{-- Delete Tenant Button (using a form for DELETE request) --}}
                            {{-- *** Uncomment this form block *** --}}
                            <form action="{{ route('owner.tenants.destroy', $tenant) }}" method="POST" style="display: inline;">
                                @csrf @method('DELETE')
                                {{-- Note: Removed problematic onclick attribute from dashboard, so leaving it off here too --}}
                                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this tenant and all their associated data?')">Delete Tenant</button> {{-- You can add a simple static onclick confirm here if you like --}}
                            </form>
                            {{-- *** End Uncommented Delete Tenant Button *** --}}


                            {{-- Send Email Button/Link ---}}
                            @if ($tenant->email) {{-- Only show if tenant has an email ---}}
                                 {{-- Use a link that goes to the compose email form for this tenant --}}
                                 {{-- Use the named route 'owner.tenants.compose-email' --}}
                                 <a href="{{ route('owner.tenants.compose-email', $tenant) }}" class="btn btn-primary">Send Email</a>
                            @endif
                            {{-- *** End Send Email Button *** --}}

                        </div> {{-- Close action-links div --}}

                    </div> {{-- Close tenant-item div --}}
                @endforeach {{-- End tenants loop --}}
            @endif {{-- End if tenants empty --}}
        </div> {{-- Close tenants-list div --}}
    </div> {{-- Close container div --}}

</body>
</html>