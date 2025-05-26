<!DOCTYPE html>
<html>
<head>
    <title>Add New Tenant for {{ $house->address ?? 'N/A' }}</title>
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
            background-color: #28a745; /* Green color for add/create */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-group button:hover {
            background-color: #218838;
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
         {{-- Back button to tenants list for this house --}}
         <a href="{{ route('owner.houses.tenants.index', $house) }}" class="back-button" style="margin-bottom: 20px;">&larr; Back to Tenant List</a>


        <h1>Add New Tenant for House: {{ $house->address ?? 'N/A' }}</h1>

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


        <form method="POST" action="{{ route('owner.houses.tenants.store', $house) }}">
            @csrf {{-- CSRF token for security --}}

            {{-- Hidden input to link tenant to this house --}}
            <input type="hidden" name="house_id" value="{{ $house->id }}">

            {{-- Tenant Name --}}
            <div class="form-group">
                <label for="name">Tenant Name:</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>

            {{-- Tenant Email --}}
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                 @error('email') <span class="error">{{ $message }}</span> @enderror
            </div>

            {{-- Tenant Phone --}}
            <div class="form-group">
                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" value="{{ old('phone') }}"> {{-- Use type="tel" for phone --}}
                 @error('phone') <span class="error">{{ $message }}</span> @enderror
            </div>

            {{-- Monthly Rent --}}
             <div class="form-group">
                <label for="rent">Monthly Rent ($):</label>
                <input type="number" id="rent" name="rent" step="0.01" value="{{ old('rent') }}" required>
                 @error('rent') <span class="error">{{ $message }}</span> @enderror
            </div>

            {{-- Add fields for rental agreement details (start date, duration) here later if needed --}}


            {{-- Submit Button --}}
            <div class="form-group">
                <button type="submit">Add Tenant</button>
            </div>

        </form>
    </div>

</body>
</html>