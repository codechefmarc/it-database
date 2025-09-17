@auth
  @if(auth()->user()->canEdit() || auth()->user()->isStudent())
    <x-nav-link href="{{ route('log') }}" :active="request()->routeIs('log')">Log Activity</x-nav-link>
  @endif
@endauth

@auth
  @if(auth())
    <x-nav-link href="{{ route('search') }}" :active="request()->routeIs('search')">Search</x-nav-link>
    <x-nav-link href="{{ route('reports') }}" :active="request()->routeIs('reports')">Reports</x-nav-link>
  @endif
@endauth

@auth
  @if(auth()->user()->isAdmin())
    <x-nav-link href="{{ route('admin.users.index') }}" :active="request()->RouteIs('admin.users.index')">Manage Users</x-nav-link>
  @endif
@endauth
