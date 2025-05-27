<!DOCTYPE html>
<html>
<head>
    <title>Welcome to {{ config('app.name') }}</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; align-items: center; min-height: 80vh; text-align: center; }
        .welcome-container { background-color: #ffffff; padding: 40px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); }
        .welcome-container h1 { color: #333; margin-bottom: 20px; }
        .welcome-container p { color: #666; font-size: 1.1em; }
    </style>
</head>
<body>

    <div class="welcome-container">
        <h1>Welcome to {{ config('app.name') }}!</h1>
        <p>Your Rental Management System.</p>

        {{-- We will add login/signup links here later --}}

    </div>

</body>
</html>