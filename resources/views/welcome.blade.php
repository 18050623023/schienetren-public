<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />
    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <div class="min-h-screen flex flex-col selection:bg-indigo-500 selection:text-white">
        <!-- Navigation -->
        <nav class="bg-white shadow-sm border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <div class="shrink-0 flex items-center">
                            <span class="font-bold text-2xl text-indigo-600">{{ config('app.name', 'Laravel') }}</span>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        @if (Route::has('login'))
                            @auth
                                <a href="{{ url('/portal') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">Portal</a>
                            @else
                                <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-indigo-600 transition-colors">Log in</a>
                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                                        Register
                                    </a>
                                @endif
                            @endauth
                        @endif
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex-grow">
            <div class="relative px-6 lg:px-8">
                <div class="mx-auto max-w-2xl py-20 sm:py-32 lg:py-40 text-center">
                    <h1 class="text-4xl font-extrabold tracking-tight text-gray-900 sm:text-6xl">
                        Supercharge your business with <span class="text-indigo-600">our platform</span>
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600">
                        Everything you need to manage your tenants and resources efficiently in one place. Choose the plan that best fits your needs.
                    </p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <a href="#pricing" class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-all">View Pricing</a>
                        <a href="{{ route('register') }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-indigo-600 transition-colors">Get started <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </div>

            <!-- Pricing Section -->
            <div id="pricing" class="py-24 sm:py-32 bg-white">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-4xl text-center">
                        <h2 class="text-base font-semibold leading-7 text-indigo-600">Pricing</h2>
                        <p class="mt-2 text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">Pricing plans for teams of all sizes</p>
                    </div>
                    <p class="mx-auto mt-6 max-w-2xl text-center text-lg leading-8 text-gray-600">Choose an affordable plan that's packed with the best features for engaging your audience, creating customer loyalty, and driving sales.</p>
                    
                    {{-- <div class="isolate mx-auto mt-16 grid max-w-md grid-cols-1 gap-y-8 sm:mt-20 lg:mx-0 lg:max-w-none lg:grid-cols-{{ min($plans->count(), 3) }} lg:gap-x-8 lg:gap-y-0"> --}}
                    <div class="isolate mx-auto mt-16 flex flex-nowrap overflow-x-auto gap-8 sm:mt-20 lg:mx-0 lg:max-w-none pb-8 snap-x px-4 sm:px-0">
                        @forelse ($plans as $plan)
                            {{-- <div class="rounded-3xl p-8 ring-1 ring-gray-200 xl:p-10 hover:shadow-xl transition-shadow bg-white flex flex-col justify-between"> --}}
                            <div class="shrink-0 w-[90%] sm:w-[350px] lg:w-[380px] snap-center rounded-3xl p-8 ring-1 ring-gray-200 xl:p-10 hover:shadow-xl transition-shadow bg-white flex flex-col justify-between">
                                <div>
                                    <h3 class="text-lg font-semibold leading-8 text-gray-900">{{ $plan->name }}</h3>
                                    <p class="mt-4 text-sm leading-6 text-gray-600">Perfect for scaling your business operations.</p>
                                    <p class="mt-6 flex items-baseline gap-x-1">
                                        <span class="text-4xl font-bold tracking-tight text-gray-900">Rp{{ number_format($plan->price, 0, ',', '.') }}</span>
                                        <span class="text-sm font-semibold leading-6 text-gray-600">/{{ $plan->duration }} {{ $plan->duration_unit }}</span>
                                    </p>
                                    <ul role="list" class="mt-8 space-y-3 text-sm leading-6 text-gray-600">
                                        @if(is_array($plan->features))
                                            @foreach($plan->features as $value)
                                            {{-- @dd($value) --}}
                                            <li class="flex gap-x-3 items-center">
                                                <svg class="h-6 w-5 flex-none text-indigo-600" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd" />
                                                </svg>
                                                <span class="font-medium text-gray-900">{{ $value['name'] }}:</span> {{ (is_bool($value['type']) || $value['type'] === 'boolean') ? 'Yes' : $value['value'] }}
                                            </li>
                                            @endforeach
                                        @else
                                            <li class="text-gray-400 italic">No specific features listed.</li>
                                        @endif
                                    </ul>
                                </div>
                                @auth
                                    {{-- Jika user sudah login, arahkan ke route checkout/langganan --}}
                                    <a href="{{ route('filament.portal.pages.checkout', ['plan_id' => $plan->id]) }}" class="mt-8 block rounded-md py-2 px-3 text-center text-sm font-semibold leading-6 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:outline-indigo-600 shadow-sm transition-colors">
                                        Checkout Now
                                    </a>
                                @else
                                    {{-- Jika user belum login, arahkan ke halaman register --}}
                                    <a href="{{ route('filament.portal.auth.register', ['plan_id' => $plan->id]) }}" class="mt-8 block rounded-md py-2 px-3 text-center text-sm font-semibold leading-6 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:outline-indigo-600 shadow-sm transition-colors">
                                        Get started today
                                    </a>
                                @endauth

                                {{-- <a href="{{ route('filament.portal.auth.register', ['plan_id' => $plan->id]) }}" class="mt-8 block rounded-md py-2 px-3 text-center text-sm font-semibold leading-6 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 bg-indigo-600 text-white hover:bg-indigo-500 focus-visible:outline-indigo-600 shadow-sm transition-colors">
                                    Get started today
                                </a> --}}
                            </div>
                        @empty
                            <div class="col-span-full text-center text-gray-500 py-10">
                                No plans available at the moment. Please check back later.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white mt-auto border-t border-gray-100">
            <div class="mx-auto max-w-7xl px-6 py-12 md:flex md:items-center md:justify-between lg:px-8">
                <div class="flex justify-center space-x-6 md:order-2">
                    <p class="text-center text-xs leading-5 text-gray-500">&copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
