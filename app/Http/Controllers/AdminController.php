<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Item;
use App\Models\Claim;
use Illuminate\Support\Facades\Mail;
use App\Mail\PostStatusMail;
use App\Mail\ClaimStatusMail;
use App\Services\ItemMatchNotificationService;

class AdminController extends Controller
{
    public function dashboard() {
        $counts = [
            'pending_posts'  => Item::where('verification_status', 'Pending')->count(),
            'approved_posts' => Item::where('verification_status', 'Approved')->count(),
            'rejected_posts' => Item::where('verification_status', 'Rejected')->count(),
            'claim_forms'    => Claim::where('claim_status', 'Pending')->count(),
            'to_be_claimed' => Item::where('verification_status', 'Approved')->where('item_status', 'Found')->whereHas('claims', function($q) {$q->where('claim_status', 'Verified'); })->count(),
            'resolved_cases' => Claim::where('claim_status', 'Resolved')->count(),
            'user_management'=> User::where('user_role', 'Student')->count(),
        ];
        return view('admin.dashboard', compact('counts'));
    }

    public function pendingPosts() {
        $posts = Item::with(['reporter.course', 'category', 'photos'])
            ->where('verification_status', 'Pending')
            ->orderBy('date_reported', 'asc')
            ->get();
        return view('admin.pending-posts', compact('posts'));
    }

    public function approvedPosts() {
        $posts = Item::with(['reporter.course', 'category', 'photos'])
            ->where('verification_status', 'Approved')
            ->orderBy('date_reported', 'desc')
            ->get();
        return view('admin.approved-posts', compact('posts'));
    }

    public function rejectedPosts() {
        $posts = Item::with(['reporter.course', 'category', 'photos'])
            ->where('verification_status', 'Rejected')
            ->orderBy('date_reported', 'desc')
            ->get();
        return view('admin.rejected-posts', compact('posts'));
    }

public function claimForms() {
    $claims = Claim::with(['item.category', 'item.photos', 'claimedBy.course'])
        ->whereIn('claim_status', ['Pending', 'Verified'])
        ->orderBy('claim_date', 'asc')
        ->get();
    return view('admin.claim-forms', compact('claims'));
}

public function toBeClaimed() {
    $posts = Item::with(['reporter.course', 'category', 'photos', 'claims.claimedBy.course'])
        ->where('verification_status', 'Approved')
        ->where('item_status', 'Found')
        ->whereHas('claims', function($q) {
            $q->where('claim_status', 'Verified');
        })
        ->orderBy('date_reported', 'asc')
        ->get();
    return view('admin.to-be-claimed', compact('posts'));
}

    public function resolvedCases(Request $request) {
        $query = Claim::with(['item.category', 'item.photos', 'claimedBy.course'])
            ->where('claim_status', 'Resolved');

        $filter_period = $request->get('period', '');
        $date_from = '';

        if (!empty($filter_period)) {
            switch ($filter_period) {
                case 'day':   $date_from = now()->subDay()->toDateString(); break;
                case 'week':  $date_from = now()->subWeek()->toDateString(); break;
                case 'month': $date_from = now()->subMonth()->toDateString(); break;
            }
            $query->whereDate('claim_date', '>=', $date_from);
        }

        $cases = $query->orderBy('claim_date', 'desc')->get();

        if ($request->get('action') === 'download') {
            return $this->downloadCsv($cases);
        }

        return view('admin.resolved-cases', compact('cases', 'filter_period', 'date_from'));
    }

    private function downloadCsv($cases) {
        if ($cases->isEmpty()) {
            return redirect()->route('admin.resolved')->with('error', 'No data to download.');
        }

        $filename = 'resolved_cases_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function() use ($cases) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Item Name', 'Category', 'Claimant Name', 'Course', 'Section', 'Email', 'Phone', 'Location', 'Resolved Date']);
            foreach ($cases as $c) {
                fputcsv($file, [
                    $c->claim_id,
                    $c->item->name,
                    $c->item->displayCategory(),
                    $c->claimer_full_name,
                    $c->claimedBy->course->course_code ?? 'N/A',
                    $c->claimedBy->section_name ?? 'N/A',
                    $c->claimedBy->email ?? 'N/A',
                    $c->claimedBy->phone_number ?? 'N/A',
                    $c->item->last_known_location ?? 'N/A',
                    $c->claim_date,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function userManagement(Request $request) {
        $show_archived = $request->get('show') === 'all';
        $query = User::with('course')->where('user_role', 'Student');
        if (!$show_archived) {
            $query->where('user_status', 'Active');
        }
        $users = $query->orderBy('full_name')->get();
        return view('admin.user-management', compact('users', 'show_archived'));
    }

    public function postAction(Request $request, ItemMatchNotificationService $matchNotifications) {
    $data   = $request->json()->all();
    $id     = $data['id'] ?? null;
    $action = $data['action'] ?? null;
    $reason = $data['reason'] ?? null;

    $item = Item::with('reporter')->find($id);
    if (!$item) {
        return response()->json(['success' => false, 'message' => 'Item not found.']);
    }

    if ($action === 'approve') {
    $item->update(['verification_status' => 'Approved', 'rejection_reason' => null]);

    // Send email to reporter
    if ($item->reporter->email) {
        Mail::to($item->reporter->email)->send(
            new PostStatusMail($item->reporter->full_name, $item->name, 'Approved')
        );
    }

    $matchNotifications->notifyMatchesForApprovedItem($item->fresh('reporter'));

    } elseif ($action === 'reject') {
        if (empty($reason)) {
            return response()->json(['success' => false, 'message' => 'Rejection reason required.']);
        }
        $item->update(['verification_status' => 'Rejected', 'rejection_reason' => $reason]);
        // Send email
        if ($item->reporter->email) {
            Mail::to($item->reporter->email)->send(
                new PostStatusMail($item->reporter->full_name, $item->name, 'Rejected', $reason)
            );
        }
    } elseif ($action === 'restore') {
        $item->update(['verification_status' => 'Pending', 'rejection_reason' => null]);
    } else {
        return response()->json(['success' => false, 'message' => 'Invalid action.']);
    }

    return response()->json(['success' => true, 'message' => "Post {$id} successfully processed."]);
}

    public function claimAction(Request $request) {
    $data   = $request->json()->all();
    $id     = $data['id'] ?? null;
    $action = $data['action'] ?? null;
    $itemId = $data['item_id'] ?? null;

    if ($action === 'resolve_by_item') {
        $claim = Claim::with(['claimedBy', 'item'])->where('item_id', $itemId)
            ->where('claim_status', 'Verified')
            ->first();

        if (!$claim) {
            return response()->json(['success' => false, 'message' => 'No verified claim found for this item.']);
        }

        $claim->update(['claim_status' => 'Resolved']);

        // Send email
        if ($claim->claimedBy->email) {
            Mail::to($claim->claimedBy->email)->send(
                new ClaimStatusMail($claim->claimedBy->full_name, $claim->item->name, 'Resolved')
            );
        }

        return response()->json(['success' => true, 'message' => 'Item resolved successfully.']);
    }

    $claim = Claim::with(['claimedBy', 'item'])->find($id);
    if (!$claim) {
        return response()->json(['success' => false, 'message' => 'Claim not found.']);
    }

    if ($action === 'set_pickup') {
        $request->validate([
            'pickup_schedule' => 'required|date',
            'pickup_location' => 'required|string|max:255',
            'pickup_notes'    => 'nullable|string',
        ]);

        $claim->update([
            'claim_status' => 'Verified',
            'pickup_schedule' => isset($data['pickup_schedule']) ? str_replace('T', ' ', $data['pickup_schedule']) : null,
            'pickup_location' => $data['pickup_location'] ?? null,
            'pickup_notes' => $data['pickup_notes'] ?? null,
        ]);
        // Send email
        if ($claim->claimedBy->email) {
            Mail::to($claim->claimedBy->email)->send(
                new ClaimStatusMail(
                    $claim->claimedBy->full_name,
                    $claim->item->name,
                    'Verified',
                    $claim->pickup_schedule,
                    $claim->pickup_location,
                    $claim->pickup_notes
                )
            );
        }
    } elseif ($action === 'reject') {
        $claim->update(['claim_status' => 'Rejected']);
        // Send email
        if ($claim->claimedBy->email) {
            Mail::to($claim->claimedBy->email)->send(
                new ClaimStatusMail($claim->claimedBy->full_name, $claim->item->name, 'Rejected')
            );
        }
    } elseif ($action === 'resolve') {
        $claim->update(['claim_status' => 'Resolved']);
        // Send email
        if ($claim->claimedBy->email) {
            Mail::to($claim->claimedBy->email)->send(
                new ClaimStatusMail($claim->claimedBy->full_name, $claim->item->name, 'Resolved')
            );
        }
    } else {
        return response()->json(['success' => false, 'message' => 'Invalid action.']);
    }

    return response()->json(['success' => true, 'message' => "Claim {$id} successfully processed."]);
}

    public function userAction(Request $request) {
        $data   = $request->json()->all();
        $id     = $data['id'] ?? null;
        $action = $data['action'] ?? null;

        if (!$id || $action !== 'delete') {
            return response()->json(['success' => false, 'message' => 'Invalid request.']);
        }

        $affected = User::where('user_id', $id)
            ->where('user_role', 'Student')
            ->where('user_status', 'Active')
            ->update(['user_status' => 'Archived']);

        if ($affected) {
            return response()->json(['success' => true, 'message' => "User {$id} archived."]);
        }

        return response()->json(['success' => false, 'message' => 'User not found or already archived.']);
    }

}
