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
        .tenants-list li { background-color: #f9f9f9; padding: 10px; margin-bottom: 8px; border-radius: 4px; border: 1px solid #ddd; }
        .tenants-list li strong { color: #007bff; }

        .add-house-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
        .add-house-button:hover {
            background-color: #0056b3;
        }

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
    </style>
</head>
<body>

    <div class="container">
        <h1>Owner Dashboard</h1>

        {{-- Add the "Add New House" button here --}}
        <a href="{{ route('owner.houses.create') }}" class="add-house-button">Add New House</a>

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

                    {{-- *** Add display for Capacity and Photo Here *** --}}

                    {{-- Display Capacity --}}
                    <p><strong>Capacity:</strong> {{ $house->capacity ?? 'N/A' }} tenants</p>

                    {{-- Display Photo (if exists) --}}
                    @if ($house->photo_path)
                        <p><strong>Photo:</strong></p>
                        {{-- Generate public URL for the stored photo using Storage facade --}}
                        <img src="{{ Storage::url($house->photo_path) }}" alt="House Photo" style="max-width: 300px; height: auto; display: block; margin-top: 10px;">
                    @else
                        <p><strong>Photo:</strong> No photo available</p>
                    @endif
                    {{-- *** End display for Capacity and Photo *** --}}


                    <div class="tenants-list">
                        <h4>Tenants:</h4>
                        @if ($house->tenants->isEmpty())
                            <p>No tenants currently linked to this house.</p>
                        @else
                            <ul>
                                @foreach ($house->tenants as $tenant)
                                    <li>
                                        <strong>{{ $tenant->name ?? 'N/A' }}</strong><br>
                                        Email: {{ $tenant->email ?? 'N/A' }}<br>
                                        Phone: {{ $tenant->phone ?? 'N/A' }}<br>
                                        Rent: ${{ number_format($tenant->rent ?? 0, 2) }}
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