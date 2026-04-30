<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Item;
use App\Models\Claim;
use App\Models\Category;
use App\Models\Course;

class StudentController extends Controller
{
    public function dashboard() {
        $user_id = session('user_id');
        $user = User::with('course')->find($user_id);
        $notification_count = 0;

        $post_notifs = Item::where('reported_by_user_id', $user_id)
            ->whereIn('verification_status', ['Approved', 'Rejected'])
            ->count();

        $claim_notifs = Claim::where('claimed_by_user_id', $user_id)
            ->whereIn('claim_status', ['Verified', 'Rejected', 'Resolved'])
            ->count();

        $notification_count = $post_notifs + $claim_notifs;

        return view('student.dashboard', compact('user', 'notification_count'));
    }

    public function reportLost() {
        $categories = Category::orderBy('name')->get();
        return view('student.report-lost', compact('categories'));
    }

    public function storeReportLost(Request $request) {
        $request->validate([
            'name'        => 'required|string|max:100',
            'category_id' => 'required|exists:categories,category_id',
            'description' => 'required|string',
            'location'    => 'required|string|max:255',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $image_url = null;
        if ($request->hasFile('image')) {
            $image_url = $request->file('image')->store('uploads/items', 'public');
        }

        Item::create([
            'name'                 => $request->name,
            'category_id'         => $request->category_id,
            'description'         => $request->description,
            'last_known_location' => $request->location,
            'latitude'            => $request->latitude,
            'longitude'           => $request->longitude,
            'image_url' => $image_url ? asset('storage/' . $image_url) : null,
            'item_status'         => 'Lost',
            'verification_status' => 'Pending',
            'reported_by_user_id' => session('user_id'),
        ]);

        return redirect()->route('student.activity')->with('success', 'Lost item reported successfully!');
    }

    public function reportFound() {
        $categories = Category::orderBy('name')->get();
        return view('student.report-found', compact('categories'));
    }

    public function storeReportFound(Request $request) {
        $request->validate([
            'name'        => 'required|string|max:100',
            'category_id' => 'required|exists:categories,category_id',
            'description' => 'required|string',
            'location'    => 'required|string|max:255',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $image_url = null;
        if ($request->hasFile('image')) {
            $image_url = $request->file('image')->store('uploads/items', 'public');
        }

        Item::create([
            'name'                 => $request->name,
            'category_id'         => $request->category_id,
            'description'         => $request->description,
            'last_known_location' => $request->location,
            'latitude'            => $request->latitude,
            'longitude'           => $request->longitude,
            'image_url' => $image_url ? asset('storage/' . $image_url) : null,
            'item_status'         => 'Found',
            'verification_status' => 'Pending',
            'reported_by_user_id' => session('user_id'),
        ]);

        return redirect()->route('student.activity')->with('success', 'Found item reported successfully!');
    }

    public function search(Request $request) {
        $query = Item::with(['category', 'reporter.course'])
            ->where('verification_status', 'Approved');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('item_status', $request->status);
        }

        $items = $query->orderBy('date_reported', 'desc')->get();
        $categories = Category::orderBy('name')->get();

        return view('student.search', compact('items', 'categories'));
    }

    public function myActivity() {
        $user_id = session('user_id');
        $posts = Item::with('category')
            ->where('reported_by_user_id', $user_id)
            ->orderBy('date_reported', 'desc')
            ->get();

        $claims = Claim::with('item.category')
            ->where('claimed_by_user_id', $user_id)
            ->orderBy('claim_date', 'desc')
            ->get();

        return view('student.my-activity', compact('posts', 'claims'));
    }

    public function settings() {
        $user = User::with('course')->find(session('user_id'));
        $courses = Course::orderBy('course_code')->get();
        return view('student.settings', compact('user', 'courses'));
    }

    public function updateSettings(Request $request) {
        $user = User::find(session('user_id'));

        $request->validate([
            'name'         => 'required|string|max:100',
            'email'        => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'phone_number' => ['required', 'regex:/^\+?\d{10,15}$/'],
            'course_id'    => 'required|exists:courses,course_id',
            'section_name' => 'required|string|max:20',
        ]);

        $user->update([
            'full_name'    => $request->name,
            'email'        => $request->email,
            'phone_number' => $request->phone_number,
            'course_id'    => $request->course_id,
            'section_name' => $request->section_name,
        ]);

        session(['user_name' => $request->name]);

        return response()->json(['success' => true]);
    }

public function updatePassword(Request $request) {
    $request->validate([
        'password' => 'required|min:6',
    ]);

    User::where('user_id', session('user_id'))
        ->update(['password_hash' => Hash::make($request->password)]);

    session()->flush();

    return response()->json(['success' => true, 'redirect' => route('login')]);
}

    public function notifications() {
        $user_id = session('user_id');

        $post_notifs = Item::where('reported_by_user_id', $user_id)
            ->whereIn('verification_status', ['Approved', 'Rejected'])
            ->get()
            ->map(fn($i) => [
                'type'      => 'POST_STATUS',
                'id'        => $i->item_id,
                'item_name' => $i->name,
                'status'    => $i->verification_status,
                'date'      => $i->date_reported,
            ]);

        $claim_notifs = Claim::with('item')
            ->where('claimed_by_user_id', $user_id)
            ->whereIn('claim_status', ['Verified', 'Rejected', 'Resolved'])
            ->get()
            ->map(fn($c) => [
                'type'      => 'CLAIM_STATUS',
                'id'        => $c->claim_id,
                'item_name' => $c->item->name,
                'status'    => $c->claim_status,
                'date'      => $c->claim_date,
            ]);

        $all = $post_notifs->concat($claim_notifs)
            ->sortByDesc('date')
            ->take(10)
            ->values();

        return response()->json(['success' => true, 'data' => $all]);
    }

    public function cancelPost(Request $request, $id) {
        $item = Item::where('item_id', $id)
            ->where('reported_by_user_id', session('user_id'))
            ->where('verification_status', 'Pending')
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Post not found or cannot be cancelled.']);
        }

        $item->delete();
        return response()->json(['success' => true, 'message' => 'Post successfully deleted.']);
    }

    public function submitClaim(Request $request) {
        $request->validate([
            'item_id'          => 'required|exists:items,item_id',
            'claim_name'       => 'required|string|max:100',
            'claim_email'      => 'required|email',
            'claim_course'     => 'required|string|max:50',
            'claim_proof_desc' => 'required|string',
            'claim_file'       => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $existing = Claim::where('item_id', $request->item_id)
            ->where('claimed_by_user_id', session('user_id'))
            ->whereIn('claim_status', ['Pending', 'Under Review', 'Verified'])
            ->first();

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'You have already claimed this item.']);
        }

        $proof_url = null;
        if ($request->hasFile('claim_file')) {
            $proof_url = $request->file('claim_file')->store('uploads/proofs', 'public');
        }

        Claim::create([
            'item_id'               => $request->item_id,
            'claimed_by_user_id'    => session('user_id'),
            'claimer_full_name'     => $request->claim_name,
            'claimer_email'         => $request->claim_email,
            'claimer_course_section'=> $request->claim_course,
            'proof_description'     => $request->claim_proof_desc,
            'proof_photo_url'       => $proof_url ? 'storage/' . $proof_url : null,
            'claim_status'          => 'Pending',
        ]);

        return response()->json(['success' => true, 'message' => 'Claim submitted successfully.']);
    }

    public function updateProfile(Request $request) {
        return $this->updateSettings($request);
    }
}