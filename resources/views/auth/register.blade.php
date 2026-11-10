<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }} - Register</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 dark:bg-gray-950 p-8 text-gray-900 dark:text-gray-300">
    <div class="max-w-4xl mx-auto bg-white dark:bg-gray-900 p-6 md:p-10 rounded-lg shadow-xl">
        <div class="mb-6 text-center">
            <h2 class="text-3xl font-bold text-gray-900 dark:text-gray-100">{{ env('APP_NAME') }}</h2>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100  mt-4">Register Your Tenant</h2>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Join to ERP Service </p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-14">
                <div>
                    <h2 class=" text-center text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4  pb-1">Your Details</h2>
                    <div class="space-y-4">
                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Full Name <sup class="text-sm text-red-400">*</sup></label>
                            <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500">
                            @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email Address -->
                        <div class="mt-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email <sup class="text-sm text-red-400">*</sup></label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500">
                            @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Phone -->
                        <div class="mt-4">
                            <label for="phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Number <sup class="text-sm text-red-400">*</sup></label>
                            <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500">
                            @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="text-center text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4  pb-1">Tenant Details</h2>
                    <div class="space-y-4">

                        <!-- Company Name -->
                        <div class="mt-4">
                            <label for="business_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Business Name <sup class="text-sm text-red-400">*</sup></label>
                            <input id="business_name" type="text" name="business_name"value="{{ old('business_name') }}" required class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500">
                            @error('business_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-4">
                            <label for="domain" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Domain Name</label>
                            <input id="domain" type="text"  placeholder="(optional)"  name="domain" value="{{ old('domain') }}" class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-yellow-500 focus:border-yellow-500">
                            @error('domain')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Subscription Plan -->
                        {{-- <div class="mt-4">
                                <label for="subscription_plan" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Choose Plan</label>
                                <select id="subscription_plan"
                                        name="subscription_plan"
                                        required
                                        class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select a plan</option>
                                    <option value="basic" {{ old('subscription_plan') == 'basic' ? 'selected' : '' }}>
                        Basic Plan - IDR 199,000/month
                        </option>
                        <option value="professional" {{ old('subscription_plan') == 'professional' ? 'selected' : '' }}>
                            Professional Plan - IDR 399,000/month
                        </option>
                        <option value="enterprise" {{ old('subscription_plan') == 'enterprise' ? 'selected' : '' }}>
                            Enterprise Plan - IDR 799,000/month
                        </option>
                        </select>
                        @error('subscription_plan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div> --}}

                </div>
            </div>
    </div>



    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-10">
    <!-- Terms -->
        <div class="mt-4">
            <label for="terms" class="inline-flex items-center">
                <input id="terms" type="checkbox" name="terms" required class="rounded border-gray-300 dark:border-gray-600 text-yellow-600 shadow-sm focus:ring-yellow-500">
                <span class="ml-2 text-sm text-gray-600">
                    I agree to the
                    <a href="#" class="text-yellow-600 hover:text-yellow-500">Terms of Service</a>
                    and
                    <a href="#" class="text-yellow-600 hover:text-yellow-500">Privacy Policy</a>
                </span>
            </label>
            @error('terms')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-300" href="{{ route('login') }}">
                Already have an account?
            </a>

            <button type="submit" class="ml-4 inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white dark:text-black uppercase tracking-widest hover:bg-yellow-500 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Start Free Trial
            </button>
        </div>
    </div>




    </form>
    </div>
</body>
</html>
