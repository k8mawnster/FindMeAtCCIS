<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckStudent {
    public function handle(Request $request, Closure $next) {
        if (!session('user_id')) {
            return redirect()->route('login');
        }
        if (session('user_role') !== 'Student') {
            return redirect()->route('admin.dashboard');
        }
        return $next($request);
    }
}