<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }} | Tenant Sign Up</title>

    {{-- Include your compiled Tailwind CSS here --}}
    {{-- Based on previous debugging, you might be using Vite: --}}
    @vite('resources/css/app.css')
    {{-- If using Laravel Mix: <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
    {{-- For local development without compilation, you might use a CDN temporarily: --}}
    {{-- <script src="https://cdn.tailwindcss.com"></script> --}}

    {{-- Add any other head elements --}}
</head>
<body class="h-full bg-white">

    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">
      <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        {{-- You can replace the logo with your own application logo --}}
        <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Tenant Sign Up</h2> {{-- *** Changed title *** --}}
      </div>

      <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        {{-- --- Tenant Registration Form --- --}}
        {{-- The form's action should point to your TENANT registration POST route --}}
        <form class="space-y-6" action="{{ route('tenant.register.post') }}" method="POST"> {{-- *** Changed route name *** --}}
           @csrf

            {{-- Display Validation Errors (if any) --}}
            @if ($errors->any())
                <div class="mb-4 font-medium text-sm text-red-600">
                    {{ __('Whoops! Something went wrong.') }}
                </div>
            @endif

          {{-- --- Name Input --- --}}
          <div>
            <label for="name" class="block text-sm/6 font-medium text-gray-900">Full Name</label>
            <div class="mt-2">
              <input type="text" name="name" id="name" autocomplete="name" required value="{{ old('name') }}" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            </div>
             @error('name')
                 <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
             @enderror
          </div>

          {{-- --- Email Input --- --}}
          <div>
            <label for="email" class="block text-sm/6 font-medium text-gray-900">Email address</label>
            <div class="mt-2">
              <input type="email" name="email" id="email" autocomplete="email" required value="{{ old('email') }}" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            </div>
             @error('email')
                 <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
             @enderror
          </div>

           {{-- *** Add Phone Number Input --- --}}
          <div>
            <label for="phone" class="block text-sm/6 font-medium text-gray-900">Phone Number (Optional)</label> {{-- Indicate optional --}}
            <div class="mt-2">
              <input type="tel" name="phone" id="phone" autocomplete="tel" value="{{ old('phone') }}" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6"> {{-- Use type="tel" --}}
            </div>
             @error('phone')
                 <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
             @enderror
          </div>
          {{-- *** End Phone Number Input *** --}}


          {{-- --- Password Input --- --}}
          <div>
            <div class="flex items-center justify-between">
              <label for="password" class="block text-sm/6 font-medium text-gray-900">Password</label>
            </div>
            <div class="mt-2">
              <input type="password" name="password" id="password" autocomplete="new-password" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            </div>
             @error('password')
                 <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
             @enderror
          </div>

          {{-- --- Password Confirmation Input --- --}}
          <div>
            <div class="flex items-center justify-between">
              <label for="password_confirmation" class="block text-sm/6 font-medium text-gray-900">Confirm Password</label>
            </div>
            <div class="mt-2">
              <input type="password" name="password_confirmation" id="password_confirmation" autocomplete="new-password" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            </div>
             @error('password_confirmation')
                 <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
             @enderror
          </div>


          <div>
            <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Sign Up</button>
          </div>
        </form>

        <p class="mt-10 text-center text-sm/6 text-gray-500">
          Already have an account?
          {{-- Link to tenant login page --}}
          <a href="{{ route('tenant.login') }}" class="font-semibold text-indigo-600 hover:text-indigo-500">Sign in here</a> {{-- *** Changed link and text *** --}}
        </p>
      </div>
    </div>

</body>
</html>