<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }} | Login</title>
    @vite('resources/css/app.css')

    {{-- *** Include your Tailwind CSS here *** --}}
    {{-- You might need to include a link to your compiled Tailwind CSS file --}}
    {{-- For example, if you are using Laravel Mix or Vite to compile CSS: --}}
    {{-- <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
    {{-- Or if you have a public/build/assets/app.css file: --}}
    {{-- @vite('resources/css/app.css') --}}
    {{-- For local development without compilation, you might use a CDN temporarily: --}}
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}

    {{-- Add any other head elements (favicon, etc.) --}}
</head>
<body class="h-full">

    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
      <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        {{-- You can replace the Tailwind logo with your own application logo --}}
        {{-- <img class="mx-auto h-10 w-auto" src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600" alt="Your Company"> --}}
        <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Sign in to your account</h2>
      </div>

      <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        {{-- --- Login Form --- --}}
        {{-- The form's action should point to your login POST route --}}
        {{-- The form's method should be POST --}}
        <form class="space-y-6" action="{{ route('login') }}" method="POST">
           @csrf {{-- *** CSRF Token for security *** --}}

            {{-- --- Display Session Error Message (e.g., from failed login attempt) --- --}}
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            {{-- --- Display Validation Errors (if any) --- --}}
            {{-- Note: Laravel's validation automatically redirects back and provides $errors variable --}}
            @if ($errors->any())
                <div class="mb-4 font-medium text-sm text-red-600">
                    {{ __('Whoops! Something went wrong.') }}
                    {{-- You can list individual errors here if you prefer --}}
                    {{-- <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul> --}}
                </div>
            @endif


          <div>
            <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
            <div class="mt-2">
                {{-- Email Input --}}
                {{-- Use name="email" --}}
                {{-- Use old('email') to repopulate if validation fails --}}
              <input type="email" name="email" id="email" autocomplete="email" required value="{{ old('email') }}" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            </div>
             {{-- Display validation error for email field specifically --}}
             @error('email')
                 <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
             @enderror
          </div>

          <div>
            <div class="flex items-center justify-between">
              <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
              <div class="text-sm">
                {{-- Forgot password link (implement this route/logic later) --}}
                <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">Forgot password?</a>
              </div>
            </div>
            <div class="mt-2">
                 {{-- Password Input --}}
                 {{-- Use name="password" --}}
              <input type="password" name="password" id="password" autocomplete="current-password" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            </div>
             {{-- Display validation error for password field specifically --}}
             @error('password')
                 <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
             @enderror
          </div>

          <div>
            {{-- Submit Button --}}
            <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Sign in</button>
          </div>
        </form>

        <p class="mt-10 text-center text-sm/6 text-gray-500">
          Not a member?
          {{-- Link to registration page (implement this route/view/logic later) --}}
          <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">Start a 14 day free trial</a>
        </p>
      </div>
    </div>

</body>
</html>