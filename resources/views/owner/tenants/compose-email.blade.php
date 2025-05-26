<!DOCTYPE html>
<html>
<head>
    <title>Compose Email to {{ $tenant->name ?? 'Tenant' }}</title>
    <style>
        /* Keep existing styles... */
        body { font-family: sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; background-color: #f9f9f9; padding: 20px; border-radius: 8px; }
        .container h1 { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="tel"],
        .form-group input[type="number"],
        .form-group input[type="file"] { /* Add file input styling */
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group textarea {
            width: 100%; /* Ensure textarea also has width */
            min-height: 150px;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-group button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-group button:hover {
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

         /* Back button style */
         .back-button {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 15px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
        }
         .back-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

    <div class="container">
         {{-- Back button to the tenant list for this house --}}
         @if ($tenant->house)
             <a href="{{ route('owner.houses.tenants.index', $tenant->house) }}" class="back-button" style="margin-bottom: 20px;">&larr; Back to Tenant List for {{ $tenant->house->address ?? 'House' }}</a>
         @else
              <a href="{{ route('owner.dashboard') }}" class="back-button" style="margin-bottom: 20px;">&larr; Back to Dashboard</a>
         @endif

        <h1>Compose Email to {{ $tenant->name ?? 'Tenant' }} ({{ $tenant->email ?? 'No Email' }})</h1>

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


        <form method="POST" action="{{ route('owner.tenants.send-email', $tenant) }}" enctype="multipart/form-data"> {{-- *** Ensure enctype is set for file uploads *** --}}
            @csrf {{-- CSRF token for security --}}

            {{-- Hidden input to pass tenant ID --}}
            <input type="hidden" name="tenant_id" value="{{ $tenant->id }}">


            {{-- Email Subject --}}
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" value="{{ old('subject') }}" required>
                @error('subject') <span class="error">{{ $message }}</span> @enderror
            </div>

            {{-- Email Body --}}
            <div class="form-group">
                <label for="body">Body:</label>
                <textarea id="body" name="body">{{ old('body') }}</textarea>
                 @error('body') <span class="error">{{ $message }}</span> @enderror
            </div>

             <div class="form-group">
                 <label for="attachments">Attachments (Optional):</label>
                 {{-- Use name="attachments[]" and multiple attribute for multiple files --}}
                 <input type="file" id="attachments" name="attachments[]" multiple>
                 @error('attachments') <span class="error">{{ $message }}</span> @enderror
                 {{-- If allowing only one file, remove the [] from name and the multiple attribute --}}
             </div>

            {{-- Submit Button --}}
            <div class="form-group">
                <button type="submit">Send Email</button>
            </div>

        </form>
    </div>

</body>
</html>