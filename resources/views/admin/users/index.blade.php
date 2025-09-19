<x-layout>
<x-slot:heading>
  Manage Users
</x-slot:heading>

<div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-md">

  <!-- Header with Create Button -->
  <div class="flex justify-between items-center mb-6">
    <h3 class="text-2xl font-semibold text-gray-900">Users</h3>
    <a href="{{ route('admin.users.create') }}"
       class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
      Create New User
    </a>
  </div>

  <!-- Users Table -->
  <div class="overflow-hidden border border-gray-200 rounded-lg">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
          <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created</th>
          <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
        </tr>
      </thead>
      <tbody class="bg-white divide-y divide-gray-200">
        @forelse($users as $user)
        <tr class="hover:bg-gray-50">
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm font-medium text-gray-900">
              {{ $user->first_name }} {{ $user->last_name }}
            </div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <div class="text-sm text-gray-900">{{ $user->email }}</div>
          </td>
          <td class="px-6 py-4 whitespace-nowrap">
            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
              {{ $user->role->name === 'admin' ? 'bg-purple-100 text-purple-800' :
                 ($user->role->name === 'data_entry' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
              {{ $user->role->display_name }}
            </span>
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
            {{ $user->created_at->format('M j, Y') }}
          </td>
          <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
            <div class="flex justify-end space-x-2">
              <a href="{{ route('admin.users.edit', $user) }}"
                 class="text-indigo-600 hover:text-indigo-900 font-medium mr-3">Edit</a>

              @if($user->id !== auth()->id())
                <button
                  form="delete-user-form-{{ $user->id }}"
                  class="text-red-600 hover:text-red-900 font-medium"
                  onclick="return confirm('Are you sure you want to delete {{ $user->first_name }} {{ $user->last_name }}?')"
                  >Delete</button>
              @else
                <span class="text-gray-400 font-medium">Current User</span>
              @endif
            </div>
          </td>
        </tr>

        <!-- Hidden delete form for each user -->
        @if($user->id !== auth()->id())
        <form id="delete-user-form-{{ $user->id }}" method="POST" action="{{ route('admin.users.destroy', $user) }}" class="hidden">
          @csrf
          @method('DELETE')
        </form>
        @endif

        @empty
        <tr>
          <td colspan="5" class="px-6 py-4 text-center text-gray-500">
            No users found. <a href="{{ route('admin.users.create') }}" class="text-indigo-600 hover:text-indigo-900">Create the first user</a>.
          </td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4 text-sm text-gray-500">
    Total users: {{ $users->count() }}
  </div>

</div>

</x-layout>
