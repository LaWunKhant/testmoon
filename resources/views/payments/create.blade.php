<!DOCTYPE html>
<html>
<head>
    <title>Record Payment</title>
    <style>
        /* Basic styling for readability */
        body { font-family: sans-serif; }
        form { margin-top: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"],
        input[type="number"],
        input[type="date"],
        select,
        textarea {
            margin-bottom: 10px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 300px; /* Adjust as needed */
        }
        button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
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

    <h1>Record New Payment</h1>

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

    <form action="{{ route('payments.store') }}" method="POST">
        @csrf {{-- CSRF token for security --}}

        <div>
            <label for="tenant_id">Tenant:</label>
            <select name="tenant_id" id="tenant_id" required>
                <option value="">Select Tenant</option>
                @foreach ($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ old('tenant_id') == $tenant->id ? 'selected' : '' }}>
                        {{ $tenant->name }} {{-- Assuming Tenant model has a 'name' attribute --}}
                    </option>
                @endforeach
            </select>
            @error('tenant_id') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="house_id">House:</label>
            <select name="house_id" id="house_id" required>
                 <option value="">Select House</option>
                @foreach ($houses as $house)
                    <option value="{{ $house->id }}" {{ old('house_id') == $house->id ? 'selected' : '' }}>
                         {{ $house->address }} {{-- Assuming House model has an 'address' attribute or similar --}}
                    </option>
                @endforeach
            </select>
             @error('house_id') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="amount">Amount:</label>
            <input type="number" name="amount" id="amount" step="0.01" required value="{{ old('amount') }}">
            @error('amount') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="payment_date">Payment Date:</label>
            <input type="date" name="payment_date" id="payment_date" required value="{{ old('payment_date') }}">
            @error('payment_date') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="payment_method">Payment Method:</label>
            <input type="text" name="payment_method" id="payment_method" value="{{ old('payment_method') }}">
            @error('payment_method') <span class="error">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="reference">Reference:</label>
            <input type="text" name="reference" id="reference" value="{{ old('reference') }}">
            @error('reference') <span class="error">{{ $message }}</span> @enderror 
        </div>

        <div>
            <label for="notes">Notes:</label>
            <textarea name="notes" id="notes">{{ old('notes') }}</textarea>
             @error('notes') <span class="error">{{ $message }}</span> @enderror
        </div>

        <button type="submit">Record Payment</button>
    </form>

</body>
</html>