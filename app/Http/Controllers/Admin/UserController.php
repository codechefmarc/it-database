<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Provides user methods.
 */
class UserController extends Controller {
  use AuthorizesRequests;

  /**
   * Shows all users.
   */
  public function index() {
    $users = User::with('role')->orderBy('created_at', 'desc')->get();
    return view('admin.users.index', compact('users'));
  }

  /**
   * Create a user form.
   */
  public function create() {
    $roles = Role::all();
    return view('admin.users.create', compact('roles'));
  }

  /**
   * Creates a user.
   */
  public function store(Request $request) {
    $request->validate([
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:8|confirmed',
      'role_id' => 'required|exists:roles,id',
    ]);

    User::create([
      'first_name' => $request->first_name,
      'last_name' => $request->last_name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      'role_id' => $request->role_id,
    ]);

    return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
  }

  /**
   * Edit an existing user form.
   */
  public function edit(User $user) {
    $roles = Role::all();
    return view('admin.users.edit', compact('user', 'roles'));
  }

  /**
   * Update existing user.
   */
  public function update(Request $request, User $user) {

    $this->authorize('edit-user', $user);

    $rules = [
      'first_name' => 'required|string|max:255',
      'last_name' => 'required|string|max:255',
      'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
    ];

    // Only validate password if it's provided.
    if ($request->filled('password')) {
      $rules['password'] = 'required|string|min:8|confirmed';
    }

    if (Auth::user()->isAdmin()) {
      $rules['role_id'] = 'required|exists:roles,id';
    }

    $request->validate($rules);

    $updateData = [
      'first_name' => $request->first_name,
      'last_name' => $request->last_name,
      'email' => $request->email,
    ];

    // Only update password if provided.
    if ($request->filled('password')) {
      $updateData['password'] = Hash::make($request->password);
    }

    if (Auth::user()->isAdmin()) {
      $updateData['role_id'] = $request->role_id;
    }

    $user->update($updateData);

    // Different redirects based on who's updating.
    if (Auth::user()->isAdmin() && Auth::id() !== $user->id) {
      return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }
    else {
      return redirect()->route('profile.edit')->with('success', 'Profile updated successfully!');
    }
  }

  /**
   * Delete a user.
   */
  public function destroy(User $user) {
    // Prevent admins from deleting themselves.
    if ($user->id === Auth::id()) {
      return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account!');
    }

    // Prevent deleting the last admin.
    if ($user->isAdmin()) {
      $adminCount = User::whereHas('role', function ($query) {
        $query->where('name', 'admin');
      })->count();

      if ($adminCount <= 1) {
        return redirect()->route('admin.users.index')->with('error', 'Cannot delete the last admin user!');
      }
    }

    $user->delete();

    return redirect()->route('admin.users.index')->with('success', 'User deleted successfully!');
  }

  /**
   * Edit profile form for the authenticated user.
   */
  public function editProfile() {
    $user = Auth::user();
    $roles = [];
    return view('admin.users.edit', compact('user', 'roles'));
  }

  /**
   * Update profile for the authenticated user.
   */
  public function updateProfile(Request $request) {
    return $this->update($request, Auth::user());
  }

}
