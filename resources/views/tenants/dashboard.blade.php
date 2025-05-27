<!DOCTYPE html>
<html>
<head>
    <title>{{ config('app.name', 'Laravel') }} | Tenant Dashboard</title>

    {{-- Include your compiled Tailwind CSS here --}}
    {{-- Based on previous debugging, you might be using Vite: --}}
    @vite('resources/css/app.css')
    {{-- If using Laravel Mix: <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
    {{-- For local development without compilation, you might use a CDN temporarily: --}}
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}

    {{-- Add any other head elements --}}

     <style>
        /* Add basic styles for house containers and list (similar to owner dashboard) */
        body { font-family: sans-serif; margin: 20px; }
        .container { max-width: 800px; margin: 0 auto; }
        .house-container { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; border-radius: 5px; }
        .house-container h3 { margin-top: 0; margin-bottom: 10px; color: #333; }
        .house-container p { margin: 5px 0; }
        .house-container img { max-width: 300px; height: auto; display: block; margin-top: 10px; }

        /* Simple button style for navigation links (optional) */
         .nav-button {
            display: inline-block;
            padding: 8px 12px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            margin-right: 10px;
        }
         .nav-button:hover { opacity: 0.9; }

     </style>

</head>
<body>

    <div class="container">
        <h1>Tenant Dashboard - Houses for Rent</h1>

         {{-- Simple Logout link for tenants --}}
         {{-- This requires a separate logout route/method for tenants if you don't want owner logout to log out tenants too --}}
         {{-- For now, let's link to the standard logout route, which will log out any authenticated user --}}
         <p>
             <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                 @csrf
                 <button type="submit" class="nav-button">Logout</button>
             </form>
         </p>


        @if ($housesForTenants->isEmpty())
            <p>No houses are currently available for rent.</p>
        @else
            @foreach ($housesForTenants as $house)
                <div class="house-container">
                    <h3>House: {{ $house->address ?? 'N/A' }}</h3>
                    <p><strong>Name:</strong> {{ $house->name ?? 'N/A' }}</p>
                    <p><strong>Price:</strong> ${{ number_format($house->price ?? 0, 2) }}</p>
                    <p><strong>Description:</strong> {{ $house->description ?? 'N/A' }}</p>
                    <p><strong>Capacity:</strong> {{ $house->capacity ?? 'N/A' }} tenants</p>

                    {{-- Display Photo using Spatie MediaLibrary (similar to owner dashboard) --}}
                    @if ($house->hasMedia('photos'))
                         <p><strong>Photo:</strong></p>
                         {{-- Get the URL of the first media item in the 'photos' collection --}}
                        <img src="{{ $house->getFirstMediaUrl('photos') }}" alt="House Photo"> {{-- Removed inline style, use CSS class later --}}
                    @else
                        <p><strong>Photo:</strong> No photo available</p>
                    @endif

                    {{-- You might add a link here to view more details about the house or inquire --}}
                    {{-- <p style="margin-top: 15px;"><a href="#" class="nav-button">View Details</a></p> --}}

                </div>
            @endforeach

            {{-- Add pagination links here if you implement pagination in the controller --}}
            {{-- {{ $housesForTenants->links() }} --}}

        @endif
    </div>

</body>
</html>