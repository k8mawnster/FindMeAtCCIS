<?php
namespace App\Services;

use App\Mail\MatchFoundMail;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

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

            if ($this->sendMatchEmail($lostItem, $foundItem)) {
                $sent++;
            }
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
            if ($this->sendMatchEmail($lostItem, $foundItem)) {
                $sent++;
            }
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
            ->get()
            ->filter(fn(Item $lostItem) => $this->itemsArePossibleMatch($lostItem, $foundItem))
            ->values();
    }

    private function matchingFoundItems(Item $lostItem)
    {
        return Item::where('item_status', 'Found')
            ->where('verification_status', 'Approved')
            ->where('item_id', '!=', $lostItem->item_id)
            ->where('reported_by_user_id', '!=', $lostItem->reported_by_user_id)
            ->get()
            ->filter(fn(Item $foundItem) => $this->itemsArePossibleMatch($lostItem, $foundItem))
            ->values();
    }

    private function itemsArePossibleMatch(Item $lostItem, Item $foundItem): bool
    {
        $sameCategory = $lostItem->category_id === $foundItem->category_id;
        $nameMatch = $this->nameMatchStrength($lostItem->name, $foundItem->name);

        return $nameMatch === 'strong'
            || ($sameCategory && $nameMatch === 'partial');
    }

    private function nameMatchStrength(string $firstName, string $secondName): ?string
    {
        $first = $this->normalizedWords($firstName);
        $second = $this->normalizedWords($secondName);

        if ($first === '' || $second === '') {
            return null;
        }

        if ($first === $second || str_contains($first, $second) || str_contains($second, $first)) {
            return 'strong';
        }

        return count(array_intersect(explode(' ', $first), explode(' ', $second))) > 0
            ? 'partial'
            : null;
    }

    private function normalizedWords(string $name): string
    {
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9]+/', ' ', $name) ?? '';

        return trim(preg_replace('/\s+/', ' ', $name) ?? '');
    }

    private function sendMatchEmail(Item $lostItem, Item $foundItem): bool
    {
        Log::info('Possible item match notification found.', [
            'lost_item_id' => $lostItem->item_id,
            'lost_item_name' => $lostItem->name,
            'found_item_id' => $foundItem->item_id,
            'found_item_name' => $foundItem->name,
            'recipient_email' => $lostItem->reporter->email,
        ]);

        try {
            Mail::to($lostItem->reporter->email)->send(
                new MatchFoundMail(
                    $lostItem->reporter->full_name,
                    $lostItem->name,
                    $foundItem->name,
                    $foundItem->last_known_location ?? 'N/A',
                    Carbon::parse($foundItem->date_reported)->format('d-m-Y')
                )
            );

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
