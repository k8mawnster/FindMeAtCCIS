<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request) {
        if (!$this->hasValidSignature($request)) {
            return response()->json(['success' => false], 403);
        }

        $payload = $request->all();
        $type    = $payload['type'] ?? 'unknown';
        $email   = $payload['data']['to'][0] ?? 'unknown';

        switch ($type) {
            case 'email.delivered':
                Log::info("Resend: Email delivered to {$email}");
                break;

            case 'email.bounced':
                Log::warning("Resend: Email bounced for {$email}");
                break;

            case 'email.complained':
                Log::warning("Resend: Spam complaint from {$email}");
                break;

            case 'email.delivery_delayed':
                Log::warning("Resend: Delivery delayed for {$email}");
                break;

            default:
                Log::info("Resend webhook received: {$type}");
                break;
        }

        return response()->json(['success' => true]);
    }

    private function hasValidSignature(Request $request): bool {
        $secret = config('services.resend.webhook_secret');
        if (!$secret) {
            Log::warning('Resend webhook rejected: missing RESEND_WEBHOOK_SECRET.');
            return false;
        }

        $id = $request->header('svix-id');
        $timestamp = $request->header('svix-timestamp');
        $signatureHeader = $request->header('svix-signature');

        if (!$id || !$timestamp || !$signatureHeader) {
            return false;
        }

        if (abs(time() - (int) $timestamp) > 300) {
            return false;
        }

        $signedContent = $id . '.' . $timestamp . '.' . $request->getContent();
        $expected = base64_encode(hash_hmac('sha256', $signedContent, $secret, true));

        foreach (explode(' ', $signatureHeader) as $signature) {
            $parts = explode(',', trim($signature), 2);
            $provided = count($parts) === 2 ? $parts[1] : $parts[0];

            if (hash_equals($expected, $provided)) {
                return true;
            }
        }

        return false;
    }
}
