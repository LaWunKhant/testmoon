<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }} | Tenant Login</title>
    @vite('resources/css/app.css')

    {{-- Include your compiled Tailwind CSS here --}}
    {{-- Based on previous debugging, you might be using Vite: --}}
    
    {{-- If using Laravel Mix: <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
    {{-- For local development without compilation, you might use a CDN temporarily: --}}
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}

    {{-- Add any other head elements --}}
</head>
<body class="h-full">

    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
      <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        {{-- You can replace the logo with your own application logo --}}
        <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Tenant Login</h2> {{-- *** Changed title *** --}}
      </div>

      <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        {{-- --- Tenant Login Form --- --}}
        {{-- The form's action should point to your TENANT login POST route --}}
        <form class="space-y-6" action="{{ route('tenant.login.post') }}" method="POST"> {{-- *** Changed route name *** --}}
           @csrf

            {{-- Display Session Error Message (if any) --}}
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Display Validation Errors (if any) --}}
            @if ($errors->any())
                <div class="mb-4 font-medium text-sm text-red-600">
                    {{ __('Whoops! Something went wrong.') }}
                </div>
            @endif

          <div>
            <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
            <div class="mt-2">
              <input type="email" name="email" id="email" autocomplete="email" required value="{{ old('email') }}" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            </div>
             @error('email')
                 <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
             @enderror
          </div>

          <div>
            <div class="flex items-center justify-between">
              <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
              <div class="text-sm">
                <a href="#" class="font-semibold text-indigo-600 hover:text-indigo-500">Forgot password?</a>
              </div>
            </div>
            <div class="mt-2">
              <input type="password" name="password" id="password" autocomplete="current-password" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            </div>
             @error('password')
                 <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
             @enderror
          </div>

          <div>
            <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Sign in</button>
          </div>
        </form>

        <p class="mt-10 text-center text-sm/6 text-gray-500">
          Not a member?
          {{-- Link to tenant registration page --}}
          <a href="{{ route('tenant.register') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Sign up for a tenant account</a> {{-- *** Changed link and text *** --}}
        </p>
      </div>
    </div>

</body>
</html>