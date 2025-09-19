<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Protects user management routes.
 */
class AdminMiddleware {

  /**
   * Protects user management routes.
   */
  public function handle(Request $request, \Closure $next) {
    if (!Auth::user() || !Auth::user()->isAdmin()) {
      abort(403, 'Access denied. Admin privileges required.');
    }

    return $next($request);
  }

}
