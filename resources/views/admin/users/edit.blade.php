<x-layout>
<x-slot:heading>
  Edit User
</x-slot:heading>

<form method="POST" action="{{
  request()->routeIs('profile.edit')
    ? route('profile.update')
    : route('admin.users.update', $user)
}}">
  @csrf
  @method('PUT')

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
                value="{{ old('first_name', $user->first_name) }}"
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
                value="{{ old('last_name', $user->last_name) }}"
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
            <div class="
              flex items-center rounded-md bg-white outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600
            ">
              <input
                id="email"
                type="email"
                name="email"
                required
                value="{{ old('email', $user->email) }}"
                @if(!Auth::user()->isAdmin()) readonly @endif
                class="
                  block min-w-0 grow py-1.5 pl-3 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6
                 read-only:bg-gray-300 read-only:cursor-not-allowed
                " />
            </div>
            @error('email')
              <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
            @enderror
          </div>
        </div>

        @if(Auth::user()->isAdmin())

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
                @foreach($roles as $role)
                  <option value="{{ $role->id }}" {{ (old('role_id', $user->role_id) == $role->id) ? 'selected' : '' }}>
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
        @endif

        <!-- Password Section -->
        <div class="border-t border-gray-200 pt-6 mt-6">
          <h4 class="text-lg font-medium text-gray-900 mb-4">Password (Optional)</h4>
          <p class="text-sm text-gray-600 mb-4">Leave blank to keep current password</p>

          <!-- New Password -->
          <div class="">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
            <div class="mt-2">
              <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                <input
                  id="password"
                  type="password"
                  name="password"
                  class="block min-w-0 grow py-1.5 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6"
                  placeholder="Enter new password (min 8 characters)" />
              </div>
              @error('password')
                <p class="text-xs text-red-500 font-semibold mt-1">{{ $message }}</p>
              @enderror
            </div>
          </div>

          <!-- Confirm New Password -->
          <div class="mt-4">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
            <div class="mt-2">
              <div class="flex items-center rounded-md bg-white pl-3 outline-1 -outline-offset-1 outline-gray-300 focus-within:outline-2 focus-within:-outline-offset-2 focus-within:outline-indigo-600">
                <input
                  id="password_confirmation"
                  type="password"
                  name="password_confirmation"
                  class="block min-w-0 grow py-1.5 pr-3 text-base text-gray-900 placeholder:text-gray-400 focus:outline-none sm:text-sm/6"
                  placeholder="Confirm new password" />
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <div class="mt-6 sm:flex max-w-2xl items-center justify-between">
      <div class="flex items-center gap-x-6 justify-self-end">
        <button type="submit" class="ml-3 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Update</button>
        <a href="{{ Auth::user()->isAdmin() ? route('admin.users.index') : route('welcome') }}" class="text-sm/6 font-semibold text-gray-900 cursor-pointer">Cancel</a>
      </div>

      @if($user->id !== auth()->id())
      <div class="flex flex-col gap-3">
        <div class="flex-col items-center">
          <button
            form="delete-user-form"
            class="block text-red-500 font-bold cursor-pointer"
            onclick="return confirm('Are you sure you want to delete {{ $user->first_name }} {{ $user->last_name }}?')"
            >Delete User</button>
        </div>
      </div>
      @endif

    </div>

    @if($user->id !== auth()->id())
    <small class="text-right block text-gray-500">Deleting a user will remove their access to the system.</small>
    @endif
  </div>

</form>

@if($user->id !== auth()->id())
<form id="delete-user-form" method="POST" action="{{ route('admin.users.destroy', $user) }}" class="hidden">
  @csrf
  @method('DELETE')
</form>
@endif

</x-layout>
