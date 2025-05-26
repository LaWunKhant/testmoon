<!DOCTYPE html>
<html>
<head>
    <title>Edit Tenant: {{ $tenant->name ?? 'N/A' }}</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #f9f9f9; padding: 20px; border-radius: 8px; }
        .container h1 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="number"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #ffc107; /* Warning/Edit color */
            color: #212529; /* Dark text */
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-group button:hover {
            background-color: #e0a800; /* Darker yellow */
        }
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
        .error {
            color: red;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

    <div class="container">
         {{-- Back button to the tenant list for this house (needs house ID, which we don't have directly here) --}}
         {{-- A better back link here might require passing the house ID to this view --}}
         {{-- For now, maybe link back to the dashboard or tenant index if you create one --}}
         {{-- <a href="{{ route('owner.dashboard') }}" class="back-button" style="margin-bottom: 20px;">&larr; Back to Dashboard</a> --}}


        <h1>Edit Tenant: {{ $tenant->name ?? 'N/A' }}</h1>

         {{-- Display success message --}}
        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        {{-- Display validation errors --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <form method="POST" action="{{ route('owner.tenants.update', $tenant) }}">
            @csrf {{-- CSRF token for security --}}
            @method('PUT') {{-- Use the PUT method for updates --}}

            {{-- Tenant Name --}}
            <div class="form-group">
                <label for="name">Tenant Name:</label>
                 {{-- Pre-fill with existing data or old input --}}
                <input type="text" id="name" name="name" value="{{ old('name', $tenant->name) }}" required>
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>

            {{-- Tenant Email --}}
            <div class="form-group">
                <label for="email">Email Address:</label>
                 {{-- Pre-fill with existing data or old input --}}
                <input type="email" id="email" name="email" value="{{ old('email', $tenant->email) }}" required>
                 @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>

            {{-- Tenant Phone --}}
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                 {{-- Pre-fill with existing data or old input --}}
                <input type="tel" id="phone" name="phone" value="{{ old('phone', $tenant->phone) }}">
                 @error('phone') <span class="error">{{ $message }}</span> @enderror
            </div>

            {{-- Monthly Rent --}}
             <div class="form-group">
                <label for="rent">Monthly Rent ($):</label>
                 {{-- Pre-fill with existing data or old input --}}
                <input type="number" id="rent" name="rent" step="0.01" value="{{ old('rent', $tenant->rent ?? 0) }}" required>
                 @error('rent') <span class="error">{{ $message }}</span> @enderror
            </div>

            {{-- Add fields for rental agreement details here later if needed --}}


            {{-- Submit Button --}}
            <div class="form-group">
                <button type="submit">Update Tenant</button>
            </div>

        </form>
    </div>

</body>
</html>