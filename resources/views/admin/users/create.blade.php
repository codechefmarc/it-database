<x-layout>
<x-slot:heading>
  Create New User
</x-slot:heading>

<form method="POST" action="{{ route('admin.users.store') }}">
  @csrf

  <div class="max-w-2xl border-b border-gray-900/10 mx-auto bg-white p-8 rounded-lg shadow-md">
    <div class="pb-12">
      <div class="mt-10 flex flex-col gap-y-2">
        <h3 class="text-2xl">User Information</h3>

        <!-- First Name -->
        <div class="">
          <label for="first_name" class="block text-sm font-medium text-gray-700 mb-2">First Name <span class="text-red-500 text-sm">*</span></label>
          <div class="mt-2">
            <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
              <input
                id="first_name"
                type="text"
                name="first_name"
                required
                value="{{ old('first_name') }}"
                class="block min-w-0 grow py-1.5 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
            </div>
            @error('first_name')
              <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <!-- Last Name -->
        <div class="">
          <label for="last_name" class="block text-sm font-medium text-gray-700 mb-2">Last Name <span class="text-red-500 text-sm">*</span></label>
          <div class="mt-2">
            <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
              <input
                id="last_name"
                type="text"
                name="last_name"
                required
                value="{{ old('last_name') }}"
                class="block min-w-0 grow py-1.5 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
            </div>
            @error('last_name')
              <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <!-- Email -->
        <div class="">
          <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email <span class="text-red-500 text-sm">*</span></label>
          <div class="mt-2">
            <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
              <input
                id="email"
                type="email"
                name="email"
                required
                value="{{ old('email') }}"
                class="block min-w-0 grow py-1.5 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
            </div>
            @error('email')
              <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <!-- Role -->
        <div class="">
          <label for="role_id" class="block text-sm font-medium text-gray-700 mb-2">Role <span class="text-red-500 text-sm">*</span></label>
          <div class="mt-2">
            <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
              <select
                id="role_id"
                name="role_id"
                required
                class="block min-w-0 grow py-1.5 pr-3 text-base text-gray-900 focus:outline-none sm:text-sm/6">
                <option value="">Select a role...</option>
                @foreach($roles as $role)
                  <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                    {{ $role->display_name }}
                  </option>
                @endforeach
              </select>
            </div>
            @error('role_id')
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
                class="block min-w-0 grow py-1.5 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
            </div>
            @error('password')
              <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        <!-- Confirm Password -->
        <div class="">
          <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password <span class="text-red-500 text-sm">*</span></label>
          <div class="mt-2">
            <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
              <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                required
                class="block min-w-0 grow py-1.5 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6" />
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="mt-6 flex items-center justify-end gap-x-6">
      <a href="{{ route('admin.users.index') }}" class="text-sm/6 font-semibold text-gray-900 cursor-pointer">Cancel</a>
      <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Create User</button>
    </div>

  </div>
</form>

</x-layout>
