<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Login routing controller.
 */
class LoginController extends Controller {

  /**
   * Redirect URL.
   *
   * @var redirectTo
   */
  protected $redirectTo = '/dashboard';

  public function __construct() {
    $this->middleware('guest')->except('logout');
    $this->middleware('auth')->only('logout');
  }

  /**
   * Returns the login form.
   */
  public function showLoginForm(): View {
    return view('auth.login');
  }

  /**
   * Provides the login logic.
   */
  public function login(Request $request): RedirectResponse {
    $request->validate([
      'email' => 'required|email',
      'password' => 'required',
    ]);

    if (Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
      $request->session()->regenerate();
      $user = Auth::user();
      if (!$user->isReadOnly()) {
        return redirect()->intended('/log');
      }
      else {
        return redirect()->intended('/search');
      }
    }

    return back()->withErrors([
      'email' => 'The provided credentials do not match our records.',
    ])->onlyInput('email');
  }

  /**
   * Provides the logout logic.
   */
  public function logout(Request $request): RedirectResponse {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
  }

}
