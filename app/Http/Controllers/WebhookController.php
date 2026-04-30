<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request) {
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
}