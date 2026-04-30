<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckAdmin {
    public function handle(Request $request, Closure $next) {
        if (!session('user_id')) {
            return redirect()->route('login');
        }
        if (session('user_role') !== 'Admin') {
            return redirect()->route('student.dashboard');
        }
        return $next($request);
    }
}