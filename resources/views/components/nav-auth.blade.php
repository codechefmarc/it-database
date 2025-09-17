@auth
  <form method="POST" action="{{ route('logout') }}" class="inline">
    @csrf
    <details class="relative">
      <summary class="flex items-center cursor-pointer text-sm font-medium mr-3">
        Hi, {{ auth()->user()->first_name }}
        <svg class="ml-1 h-4 w-4 transition-transform" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </summary>
      <div class="mt-2 shadow-lg rounded-md py-1 bg-gray-600 absolute right-[50%] transform-[translateX(50%)]">
        <a href="{{ auth()->user()->isAdmin() ? route('admin.users.edit', auth()->user()->id) : route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-gray-700">
          Profile
        </a>
        <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-700 cursor-pointer">
          Logout
        </button>
      </div>
  </form>
@else
  <a href="{{ route('login') }}" class="ml-auto ext-gray-900 hover:bg-gray-700 hover:text-white block rounded-md px-3 py-2 text-sm font-medium">
    Login
  </a>
@endauth
