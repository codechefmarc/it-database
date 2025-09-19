<x-layout>
<x-slot:heading>
  Login
</x-slot:heading>

<form method="POST" action="{{ route('login') }}">
  @csrf

  <div class="max-w-2xl border-b border-gray-900/10 mx-auto bg-white p-8 rounded-lg shadow-md">
    <div class="pb-12">
      <div class="mt-10 flex flex-col gap-y-2">
        <h3 class="text-2xl">Sign In</h3>

        <!-- Email -->
        <div class="">
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email Address <span class="text-red-500 text-sm">*</span></label>
          <div class="mt-2">
            <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
              <input
                id="email"
                type="email"
                name="email"
                required
                autofocus
                autocomplete="email"
                value="{{ old('email') }}"
                class="block min-w-0 grow py-1.5 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
            </div>
            @error('email')
              <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <!-- Password -->
        <div class="">
          <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password <span class="text-red-500 text-sm">*</span></label>
          <div class="mt-2">
            <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
              <input
                id="password"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                class="block min-w-0 grow py-1.5 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
            </div>
            @error('password')
              <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <!-- Remember Me -->
        <div class="flex items-center mt-4">
          <input
            id="remember"
            type="checkbox"
            name="remember"
            {{ old('remember') ? 'checked' : '' }}
            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded" />
          <label for="remember" class="ml-2 block text-sm text-gray-700">
            Remember Me
          </label>
        </div>

      </div>
    </div>

    <div class="mt-6 flex items-center justify-end gap-x-6">
      <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
        Sign In
      </button>
    </div>

  </div>
</form>

</x-layout>
