<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ForgotPasswordMail;

class AuthController extends Controller
{
    // Show login page
    public function showLogin() {
    if (session('user_id')) {
        return redirect()->route(strtolower(session('user_role')) . '.dashboard');
    }
    return view('auth.login');
}

    // Handle login
    public function login(Request $request) {
        $request->validate([
            'student_id' => 'required',
            'password'   => 'required',
        ]);

        $user = User::where('student_id', $request->student_id)
                    ->where('user_status', 'Active')
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password_hash)) {
            return back()->with('error', 'Invalid Student ID or password.')->withInput();
        }

        session([
            'user_id'   => $user->user_id,
            'user_name' => $user->full_name,
            'user_role' => $user->user_role,
        ]);
        $request->session()->regenerate();

        return redirect()->route(strtolower($user->user_role) . '.dashboard');
    }

    // Show register page
    public function showRegister() {
        $courses = Course::orderBy('course_code')->get();
        return view('auth.register', compact('courses'));
    }

    // Handle register
public function register(Request $request) {
    $request->validate([
        'student_id'   => ['required', 'regex:/^\d{2}-\d{6}$/'],
        'fullname'     => 'required|string|max:100',
        'email'        => 'required|email|unique:users,email',
        'phone_number' => ['required', 'regex:/^\+?\d{10,15}$/'],
        'course'       => 'required|exists:courses,course_id',
        'year_level'   => 'required',
        'section'      => 'required',
        'password'     => 'required|min:6',
    ]);

    $existing = User::where('student_id', $request->student_id)
                    ->orWhere('email', $request->email)
                    ->first();

    if ($existing) {
        return back()->with('error', 'Student ID or Email already registered.')->withInput();
    }

    User::create([
        'student_id'    => $request->student_id,
        'full_name'     => $request->fullname,
        'email'         => $request->email,
        'phone_number'  => $request->phone_number,
        'password_hash' => Hash::make($request->password),
        'course_id'     => $request->course,
        'section_name'  => $request->year_level . '-' . $request->section,
        'user_role'     => 'Student',
        'user_status'   => 'Active',
    ]);

    return redirect()->route('login')->with('success', 'Registration successful! Please log in.');
}

    // Show forgot password page
    public function showForgotPassword() {
        return view('auth.forgot-password');
    }

    // Handle forgot password
public function forgotPassword(Request $request) {
    $request->validate(['email_or_id' => 'required']);

$user = User::where(function($query) use ($request) {
        $query->where('email', $request->email_or_id)
              ->orWhere('student_id', $request->email_or_id);
    })
    ->where('user_status', 'Active')
    ->first();

    if (!$user) {
        return back()->with('success', 'If the account exists, a reset link has been sent.');
    }

    try {
        $token = bin2hex(random_bytes(32));

        DB::table('password_resets')->updateOrInsert(
            ['email' => $user->email],
            ['token' => hash('sha256', $token), 'created_at' => now()]
        );

        Mail::to($user->email)->send(new ForgotPasswordMail($user->full_name, $token));

        return back()->with('success', 'If the account exists, a reset link has been sent.');

    } catch (\Exception $e) {
        Log::warning('Failed to send password reset email.', [
            'recipient' => $user->email,
            'error' => $e->getMessage(),
        ]);

        report($e);
        return back()->with('success', 'If the account exists, a reset link has been sent.');
    }
}

    // Logout
    public function logout() {
        session()->flush();
        return redirect()->route('login');
    }

    public function showResetPassword(Request $request) {
    $token = $request->get('token');
    if (!$token) return redirect()->route('login');
    return view('auth.reset-password', compact('token'));
}

public function resetPassword(Request $request) {
    $request->validate([
        'token'    => 'required',
        'password' => 'required|min:6|confirmed',
    ]);

    $hashed = hash('sha256', $request->token);
    $record = DB::table('password_resets')
                ->where('token', $hashed)
                ->where('created_at', '>=', now()->subMinutes(60))
                ->first();

    if (!$record) {
        return back()->with('error', 'This reset link is invalid or has expired.');
    }

    $user = User::where('email', $record->email)->first();
    if (!$user) {
        return back()->with('error', 'User not found.');
    }

    $user->update(['password_hash' => Hash::make($request->password)]);
    DB::table('password_resets')->where('email', $record->email)->delete();

    return redirect()->route('login')->with('success', 'Password reset successful! Please log in.');
}
}
