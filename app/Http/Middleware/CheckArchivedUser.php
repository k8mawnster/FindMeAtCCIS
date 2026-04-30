<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class CheckArchivedUser {
    public function handle(Request $request, Closure $next) {
        if (session('user_id')) {
            $user = User::find(session('user_id'));
            if (!$user || $user->user_status === 'Archived') {
                session()->flush();
                return redirect()->route('login')
                    ->with('error', 'Your account has been disabled. Please contact the administrator.');
            }
        }
        return $next($request);
    }
}