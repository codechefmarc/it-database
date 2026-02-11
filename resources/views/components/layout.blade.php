<!DOCTYPE html>
<html class="h-full bg-gray-100" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image">
    <title>{{ $heading }} - {{ config('app.name') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
      @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <script src="https://kit.fontawesome.com/d6c74f4872.js" crossorigin="anonymous"></script>
  </head>
  <body class="h-full">

    <div class="min-h-full">
    <div class="bg-gray-600 pt-1 pb-1 {{ env('APP_ENV') === 'local' ? 'bg-yellow-600' : '' }} text-white flex justify-end items-center">
      @if(env('APP_ENV') === 'local')
        <p class="mr-auto ml-3">DEVELOPMENT</p>
      @endif
      <x-nav-auth/>
    </div>
    <nav class="bg-white-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
          <div class="flex h-16 items-center justify-between">
            <div class="flex items-center">
              <div class="shrink-0">
                <a href="{{ url('/') }}"><img src="{{ asset('images/srjc-logo-wide.webp') }}" alt="Santa Rosa Junior College" class="w-50 h-auto" /></a>
              </div>
              <div class="hidden md:block">
                <div class="ml-10 flex items-baseline space-x-4">
                  <x-nav></x-nav>
                </div>
              </div>
            </div>

            <div class="-mr-2 flex md:hidden">
              <!-- Mobile menu button -->
              <button type="button" command="--toggle" commandfor="mobile-menu" class="relative inline-flex items-center justify-center rounded-md bg-white-800 p-2 text-gray-900 focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800 focus:outline-hidden">
                <span class="absolute -inset-0.5"></span>
                <span class="sr-only">Open main menu</span>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 in-aria-expanded:hidden">
                  <path d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" data-slot="icon" aria-hidden="true" class="size-6 not-in-aria-expanded:hidden">
                  <path d="M6 18 18 6M6 6l12 12" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <el-disclosure id="mobile-menu" hidden class="block md:hidden">
          <div class="space-y-1 px-2 pt-2 pb-3 sm:px-3">
            <x-nav></x-nav>
          </div>

        </el-disclosure>
      </nav>

      <header class="bg-white shadow-sm">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
          <h1 class="text-3xl font-bold tracking-tight text-gray-900">{{ config('app.name') }}</h1>
          <h2 class="font-bold text-2xl text-gray-600">{{ $heading }}</h2>
        </div>
      </header>
      <main>

        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">

            {{-- Flash Messages --}}
            @if(session('info'))
              <div class="mt-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded mb-4">
                {{ session('info') }}
              </div>
            @endif
            @if(session('success'))
              <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
              </div>
            @endif

            @if(session('error'))
              <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                {{ session('error') }}
              </div>
            @endif

          {{ $slot }}
        </div>
      </main>
    </div>


        @if (Route::has('login'))
            <div class="h-14.5 hidden lg:block"></div>
        @endif

    <script>
      window.apiRoutes = {
        campuses: '{{ route("getCampuses") }}',
        buildings: '{{ route("getBuildings") }}',
        assetMakes: '{{ route("getAssetMakes") }}',
        assetTeams: '{{ route("getAssetTeams") }}',
        assetModels: '{{ route("getAssetModels") }}',
        stockRooms: '{{ route("getStockRooms") }}',
        deviceTypes: '{{ route("getDeviceTypes") }}',
        templates: '{{ route("getTemplates") }}',
        searchAssets: '{{ route("searchAssets") }}',
      };
    </script>

  </body>
</html>
