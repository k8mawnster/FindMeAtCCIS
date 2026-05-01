<?php
namespace App\Services;

use App\Mail\MatchFoundMail;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ItemMatchNotificationService
{
    public function notifyMatchesForApprovedItem(Item $item): int
    {
        $item->loadMissing('reporter');

        Log::info('Checking approved item for possible matches.', [
            'item_id' => $item->item_id,
            'item_name' => $item->name,
            'item_status' => $item->item_status,
            'verification_status' => $item->verification_status,
            'category_id' => $item->category_id,
            'reported_by_user_id' => $item->reported_by_user_id,
        ]);

        if ($item->verification_status !== 'Approved') {
            Log::info('Skipped item match check because item is not approved.', [
                'item_id' => $item->item_id,
                'verification_status' => $item->verification_status,
            ]);

            return 0;
        }

        return $item->item_status === 'Found'
            ? $this->notifyLostReporters($item)
            : $this->notifyLostReporterAboutFoundItems($item);
    }

    private function notifyLostReporters(Item $foundItem): int
    {
        $sent = 0;
        $matchingLostItems = $this->matchingLostItems($foundItem);

        Log::info('Possible lost item matches checked for approved found item.', [
            'found_item_id' => $foundItem->item_id,
            'found_item_name' => $foundItem->name,
            'matches_found' => $matchingLostItems->count(),
        ]);

        $matchingLostItems->each(function (Item $lostItem) use ($foundItem, &$sent) {
            if (!$lostItem->reporter?->email) {
                Log::warning('Skipped possible match notification because lost item reporter has no email.', [
                    'lost_item_id' => $lostItem->item_id,
                    'found_item_id' => $foundItem->item_id,
                ]);

                return;
            }

            $this->sendMatchEmail($lostItem, $foundItem);
            $sent++;
        });

        return $sent;
    }

    private function notifyLostReporterAboutFoundItems(Item $lostItem): int
    {
        if (!$lostItem->reporter?->email) {
            Log::warning('Skipped possible match notification because lost item reporter has no email.', [
                'lost_item_id' => $lostItem->item_id,
            ]);

            return 0;
        }

        $sent = 0;
        $matchingFoundItems = $this->matchingFoundItems($lostItem);

        Log::info('Possible found item matches checked for approved lost item.', [
            'lost_item_id' => $lostItem->item_id,
            'lost_item_name' => $lostItem->name,
            'matches_found' => $matchingFoundItems->count(),
        ]);

        $matchingFoundItems->each(function (Item $foundItem) use ($lostItem, &$sent) {
            $this->sendMatchEmail($lostItem, $foundItem);
            $sent++;
        });

        return $sent;
    }

    private function matchingLostItems(Item $foundItem)
    {
        return Item::with('reporter')
            ->where('item_status', 'Lost')
            ->where('verification_status', 'Approved')
            ->where('item_id', '!=', $foundItem->item_id)
            ->where('reported_by_user_id', '!=', $foundItem->reported_by_user_id)
            ->where(function ($query) use ($foundItem) {
                $query->where('category_id', $foundItem->category_id)
                    ->orWhere('name', 'like', '%' . $foundItem->name . '%')
                    ->orWhereRaw('? LIKE CONCAT("%", name, "%")', [$foundItem->name]);
            })
            ->get();
    }

    private function matchingFoundItems(Item $lostItem)
    {
        return Item::where('item_status', 'Found')
            ->where('verification_status', 'Approved')
            ->where('item_id', '!=', $lostItem->item_id)
            ->where('reported_by_user_id', '!=', $lostItem->reported_by_user_id)
            ->where(function ($query) use ($lostItem) {
                $query->where('category_id', $lostItem->category_id)
                    ->orWhere('name', 'like', '%' . $lostItem->name . '%')
                    ->orWhereRaw('? LIKE CONCAT("%", name, "%")', [$lostItem->name]);
            })
            ->get();
    }

    private function sendMatchEmail(Item $lostItem, Item $foundItem): void
    {
        Log::info('Possible item match notification found.', [
            'lost_item_id' => $lostItem->item_id,
            'lost_item_name' => $lostItem->name,
            'found_item_id' => $foundItem->item_id,
            'found_item_name' => $foundItem->name,
            'recipient_email' => $lostItem->reporter->email,
        ]);

        Mail::to($lostItem->reporter->email)->send(
            new MatchFoundMail(
                $lostItem->reporter->full_name,
                $lostItem->name,
                $foundItem->name,
                $foundItem->last_known_location ?? 'N/A',
                Carbon::parse($foundItem->date_reported)->format('d-m-Y')
            )
        );
    }
}
