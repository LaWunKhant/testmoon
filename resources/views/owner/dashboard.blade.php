<!DOCTYPE html>
<html>
<head>
    <title>Owner Dashboard</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .house-container { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .house-container h3 { margin-top: 0; margin-bottom: 10px; color: #333; }
        .house-container p { margin: 5px 0; }
        .tenants-list { margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px; }
        .tenants-list h4 { margin-top: 0; margin-bottom: 10px; color: #555; }
        .tenants-list ul { list-style: none; padding: 0; margin: 0; }
        .tenants-list li { background-color: #f9f9f9; padding: 10px; margin-bottom: 8px; border-radius: 4px; border: 1px solid #ddd; position: relative; }
        .tenants-list li strong { color: #007bff; }

        /* --- Simplified and Corrected Button Styles --- */
        .btn {
            display: inline-block; /* Buttons and links display inline */
            padding: 8px 12px; /* Base padding */
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px; /* Base font size */
            cursor: pointer;
            border: none; /* Remove default button border */
            margin-right: 5px; /* Space between buttons */
            text-align: center; /* Center text within buttons */
        }

        /* Specific Button Colors */
        .btn-primary { background-color: #007bff; } /* Blue */
        .btn-success { background-color: #28a745; } /* Green */
        .btn-warning { background-color: #ffc107; color: #212529; } /* Yellow */
        .btn-danger { background-color: #dc3545; color: white; } /* Red */
        .btn-secondary { background-color: #6c757d; color: white; } /* Gray */


        .btn:hover { opacity: 0.9; } /* Simple hover effect */

        /* --- End Simplified Button Styles --- */


         /* Added style for alert messages */
         .alert {
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid transparent;
            border-radius: 4px;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
         .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        /* Styles for tenant item layout */
         .tenant-item-content {
             display: inline-block;
             margin-right: 15px;
             vertical-align: top;
         }

         .tenant-actions {
             display: inline-block;
             vertical-align: top;
         }

         /* Specific tenant action button overrides if needed (applying btn styles directly is usually sufficient) */
         /* .tenant-action-link, .tenant-action-button {
             font-size: 12px;
             padding: 3px 8px;
             margin-left: 10px;
         } */


    </style>
</head>
<body>

    <div class="container">
        <h1>Owner Dashboard</h1>

        {{-- *** Add the "Add New House" button here (OUTSIDE the house loop) *** --}}
        <p style="margin-bottom: 20px;">
             <a href="{{ route('owner.houses.create') }}" class="btn btn-primary">Add New House</a>

             {{-- *** Add Logout Button Here *** --}}
             {{-- Use a form to send a POST request to the logout route --}}
             <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                 @csrf {{-- CSRF token --}}
                 <button type="submit" class="btn btn-secondary">Logout</button> {{-- Use a secondary button style (gray) --}}
             </form>
             {{-- *** End Logout Button *** --}}
        </p>


        {{-- Display success message from session flash --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif
         {{-- Display error message from session flash --}}
         @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif


        @if ($ownerHouses->isEmpty())
            <p>You do not currently own any houses.</p>
        @else
            @foreach ($ownerHouses as $house)
                <div class="house-container">
                    <h3>House: {{ $house->address ?? 'N/A' }}</h3>
                    <p><strong>Name:</strong> {{ $house->name ?? 'N/A' }}</p>
                    <p><strong>Price:</strong> ${{ number_format($house->price ?? 0, 2) }}</p>
                    <p><strong>Description:</strong> {{ $house->description ?? 'N/A' }}</p>

                    {{-- Display Capacity and Photo --}}
                    <p><strong>Capacity:</strong> {{ $house->capacity ?? 'N/A' }} tenants</p>
                    @if ($house->hasMedia('photos'))
                         <p><strong>Photo:</strong></p>
                        {{-- Get the URL of the first media item in the 'photos' collection --}}
                        <img src="{{ $house->getFirstMediaUrl('photos') }}" alt="House Photo" style="max-width: 300px; height: auto; display: block; margin-top: 10px;">
                    @else
                        <p><strong>Photo:</strong> No photo available</p>
                    @endif

                    {{-- Action Buttons for House (Add Tenant, Edit House, Delete House) --}}
                    {{-- Placing these buttons in a paragraph for layout --}}
                    <p style="margin-top: 20px;">
                        {{-- Add Tenant Button/Link --}}
                        {{-- Use the named route 'owner.houses.tenants.create' and pass the $house model --}}
                        <a href="{{ route('owner.houses.tenants.create', $house) }}" class="btn btn-success">Add Tenant</a>

                        {{-- Edit House Button --}}
                        {{-- Use the named route 'owner.houses.edit' and pass the $house model --}}
                        <a href="{{ route('owner.houses.edit', $house) }}" class="btn btn-warning">Edit House</a>

                        {{-- Delete House Button (using a form for DELETE request) --}}
                        {{-- Use the named route 'owner.houses.destroy' and pass the $house model --}}
                        <form action="{{ route('owner.houses.destroy', $house) }}" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">Delete House</button> {{-- Removed onclick here --}}
                        </form>
                    </p>


                    <div class="tenants-list">
                        <h4>Tenants:</h4>
                        @if ($house->tenants->isEmpty())
                            <p>No tenants currently linked to this house.</p>
                        @else
                            <ul>
                                @foreach ($house->tenants as $tenant)
                                    <li>
                                        {{-- Display Tenant details --}}
                                        <div class="tenant-item-content">
                                             <strong>{{ $tenant->name ?? 'N/A' }}</strong><br>
                                            Email: {{ $tenant->email ?? 'N/A' }}<br>
                                            Phone: {{ $tenant->phone ?? 'N/A' }}<br>
                                            Rent: ${{ number_format($tenant->rent ?? 0, 2) }}
                                        </div>


                                        {{-- Edit and Delete Tenant Buttons/Links (on the tenant item) and Send Email Button --}}
                                        <div class="tenant-actions">
                                            {{-- Edit Tenant Link --}}
                                            {{-- Use the named route 'owner.tenants.edit' and pass the $tenant model --}}
                                            <a href="{{ route('owner.tenants.edit', $tenant) }}" class="btn btn-warning">Edit</a>

                                            {{-- Delete Tenant Button (using a form for DELETE request) --}}
                                            {{-- Use the named route 'owner.tenants.destroy' and pass the $tenant model --}}
                                            <form action="{{ route('owner.tenants.destroy', $tenant) }}" method="POST" style="display: inline;">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-danger">Delete</button> {{-- Removed onclick here --}}
                                            </form>

                                            {{-- Send Email Button/Link (if tenant has email) --}}
                                            @if ($tenant->email)
                                                 {{-- Use a link that goes to the compose email form for this tenant --}}
                                                 {{-- Use the named route 'owner.tenants.compose-email' --}}
                                                 <a href="{{ route('owner.tenants.compose-email', $tenant) }}" class="btn btn-primary" style="margin-left: 10px;">Send Email</a>
                                            @endif
                                        </div>

                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>

</body>
</html>