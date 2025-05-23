<!DOCTYPE html>
<html>
<head>
    <title>Add New House</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .container { max-width: 600px; margin: 0 auto; background-color: #f9f9f9; padding: 20px; border-radius: 8px; }
        .container h1 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
         .form-group input[type="file"] {
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
        <h1>Add New House</h1>

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


        <form method="POST" action="{{ route('owner.houses.store') }}" enctype="multipart/form-data">
            @csrf {{-- CSRF token for security --}}

            {{-- House Name --}}
            <div class="form-group">
                <label for="name">House Name:</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required>
                @error('name') <span class="error">{{ $message }}</span> @enderror
            </div>

            {{-- House Address --}}
            <div class="form-group">
                <label for="address">Address:</label>
                <input type="text" id="address" name="address" value="{{ old('address') }}" required>
                 @error('address') <span class="error">{{ $message }}</span> @enderror
            </div>

            {{-- Description --}}
            <div class="form-group">
                <label for="description">Description:</label>
                <textarea id="description" name="description">{{ old('description') }}</textarea>
                 @error('description') <span class="error">{{ $message }}</span> @enderror
            </div>

            {{-- Price --}}
             <div class="form-group">
                <label for="price">Rental Price ($):</label>
                <input type="number" id="price" name="price" step="0.01" value="{{ old('price') }}" required>
                 @error('price') <span class="error">{{ $message }}</span> @enderror
            </div>

             {{-- Capacity (for Sharehouse/WG) --}}
             <div class="form-group">
                <label for="capacity">Capacity (Number of Tenants):</label>
                <input type="number" id="capacity" name="capacity" min="1" value="{{ old('capacity') ?? 1 }}" required>
                 @error('capacity') <span class="error">{{ $message }}</span> @enderror
            </div>

             {{-- Photo Upload --}}
             <div class="form-group">
                 <label for="photo">House Photo:</label>
                 <input type="file" id="photo" name="photo" accept="image/*"> {{-- accept="image/*" restricts file types --}}
                  @error('photo') <span class="error">{{ $message }}</span> @enderror
             </div>


            {{-- Submit Button --}}
            <div class="form-group">
                <button type="submit">Add House</button>
            </div>

        </form>
    </div>

</body>
</html>