<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;
use App\Models\Claim;
use App\Models\Category;
use App\Models\Course;
use App\Models\ItemPhoto;

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
            'images.*'    => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'custom_category' => 'nullable|string|max:80',
        ]);
        $customCategory = $this->validatedCustomCategory($request);

        $item = Item::create([
            'name'                 => $request->name,
            'category_id'         => $request->category_id,
            'custom_category'     => $customCategory,
            'description'         => $request->description,
            'last_known_location' => $request->location,
            'latitude'            => $request->latitude,
            'longitude'           => $request->longitude,
            'image_url'            => null,
            'item_status'         => 'Lost',
            'verification_status' => 'Pending',
            'reported_by_user_id' => session('user_id'),
        ]);

        $this->storeItemPhotos($request, $item);

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
            'images.*'    => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'custom_category' => 'nullable|string|max:80',
        ]);
        $customCategory = $this->validatedCustomCategory($request);

        $item = Item::create([
            'name'                 => $request->name,
            'category_id'         => $request->category_id,
            'custom_category'     => $customCategory,
            'description'         => $request->description,
            'last_known_location' => $request->location,
            'latitude'            => $request->latitude,
            'longitude'           => $request->longitude,
            'image_url'            => null,
            'item_status'         => 'Found',
            'verification_status' => 'Pending',
            'reported_by_user_id' => session('user_id'),
        ]);

        $this->storeItemPhotos($request, $item);

        return redirect()->route('student.activity')->with('success', 'Found item reported successfully!');
    }

    public function search(Request $request) {
        $query = Item::with(['category', 'reporter.course', 'photos'])
            ->where('verification_status', 'Approved');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('custom_category', 'like', '%' . $request->search . '%');
            });
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

    public function showItem($id) {
        $item = Item::with(['category', 'reporter.course', 'photos'])
            ->where('verification_status', 'Approved')
            ->findOrFail($id);

        $similarItems = $this->possibleMatchesFor($item)
            ->with(['category', 'photos'])
            ->take(6)
            ->get();

        return view('student.item-detail', compact('item', 'similarItems'));
    }

    public function myActivity() {
        $user_id = session('user_id');
        $posts = Item::with(['category', 'photos'])
            ->where('reported_by_user_id', $user_id)
            ->orderBy('date_reported', 'desc')
            ->get();

        $claims = Claim::with('item.category', 'item.photos')
            ->where('claimed_by_user_id', $user_id)
            ->orderBy('claim_date', 'desc')
            ->get();

        return view('student.my-activity', compact('posts', 'claims'));
    }

    public function editReport($id) {
        $item = Item::with(['category', 'photos'])
            ->where('reported_by_user_id', session('user_id'))
            ->where('verification_status', 'Pending')
            ->findOrFail($id);

        $categories = Category::orderBy('name')->get();
        return view('student.edit-report', compact('item', 'categories'));
    }

    public function updateReport(Request $request, $id) {
        $item = Item::where('reported_by_user_id', session('user_id'))
            ->where('verification_status', 'Pending')
            ->findOrFail($id);

        $request->validate([
            'name'        => 'required|string|max:100',
            'category_id' => 'required|exists:categories,category_id',
            'description' => 'required|string',
            'location'    => 'required|string|max:255',
            'latitude'    => 'nullable|numeric',
            'longitude'   => 'nullable|numeric',
            'images.*'    => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
            'custom_category' => 'nullable|string|max:80',
        ]);
        $customCategory = $this->validatedCustomCategory($request);

        $item->update([
            'name'                 => $request->name,
            'category_id'          => $request->category_id,
            'custom_category'      => $customCategory,
            'description'          => $request->description,
            'last_known_location'  => $request->location,
            'latitude'             => $request->latitude,
            'longitude'            => $request->longitude,
        ]);

        $this->storeItemPhotos($request, $item);

        return redirect()->route('student.activity')->with('success', 'Report updated successfully.');
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

    private function storeItemPhotos(Request $request, Item $item): void {
        $files = [];
        if ($request->hasFile('images')) {
            $files = $request->file('images');
        } elseif ($request->hasFile('image')) {
            $files = [$request->file('image')];
        }

        foreach ($files as $file) {
            if (!$file) {
                continue;
            }

            $path = $file->store('uploads/items', 'public');
            $url = asset('storage/' . $path);

            ItemPhoto::create([
                'item_id' => $item->item_id,
                'image_url' => $url,
            ]);

            if (!$item->image_url) {
                $item->update(['image_url' => $url]);
            }
        }
    }

    private function validatedCustomCategory(Request $request): ?string {
        $category = Category::find($request->category_id);
        if (strcasecmp($category->name ?? '', 'Other') !== 0) {
            return null;
        }

        $request->validate([
            'custom_category' => 'required|string|max:80',
        ]);

        return trim($request->custom_category);
    }

    private function possibleMatchesFor(Item $item) {
        $oppositeStatus = $item->item_status === 'Found' ? 'Lost' : 'Found';

        return Item::where('verification_status', 'Approved')
            ->where('item_status', $oppositeStatus)
            ->where('item_id', '!=', $item->item_id)
            ->where('reported_by_user_id', '!=', $item->reported_by_user_id)
            ->where(function ($query) use ($item) {
                $query->where('category_id', $item->category_id)
                    ->orWhere('name', 'like', '%' . $item->name . '%')
                    ->orWhereRaw('? LIKE CONCAT("%", name, "%")', [$item->name]);

                if ($item->custom_category) {
                    $query->orWhere('custom_category', 'like', '%' . $item->custom_category . '%');
                }
            })
            ->orderByRaw('category_id = ? desc', [$item->category_id])
            ->orderBy('date_reported', 'desc');
    }
}
