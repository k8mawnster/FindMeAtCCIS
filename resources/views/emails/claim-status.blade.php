<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: white; max-width: 500px; margin: auto; border-radius: 10px; padding: 30px; }
        .header { background: #2c7a4b; color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
        .status-verified { color: #2c7a4b; font-weight: bold; }
        .status-rejected { color: #cc0000; font-weight: bold; }
        .status-resolved { color: #1a6fbf; font-weight: bold; }
        .footer { text-align: center; font-size: 0.8em; color: #999; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>FindMe@CCIS</h2>
            <p>Lost and Found Management System</p>
        </div>
        <div style="padding: 20px;">
            <p>Hello, <strong>{{ $studentName }}</strong>!</p>
            <p>Your claim for the item <strong>"{{ $itemName }}"</strong> has been:</p>
            <p class="status-{{ strtolower($status) }}" style="font-size: 1.3em;">
                {{ strtoupper($status) }}
            </p>
            @if(strtolower($status) === 'verified')
                <p>Please visit the CCIS Lost and Found office to pick up your item. Bring a valid ID.</p>
                @if($pickupSchedule || $pickupLocation || $pickupNotes)
                    <div style="background: #f4fbf6; border-left: 4px solid #2c7a4b; padding: 12px; margin: 15px 0;">
                        @if($pickupSchedule)
                            <p style="margin: 0 0 8px;"><strong>Pickup Schedule:</strong> {{ \Carbon\Carbon::parse($pickupSchedule)->format('F j, Y g:i A') }}</p>
                        @endif
                        @if($pickupLocation)
                            <p style="margin: 0 0 8px;"><strong>Pickup Location:</strong> {{ $pickupLocation }}</p>
                        @endif
                        @if($pickupNotes)
                            <p style="margin: 0;"><strong>Notes:</strong> {{ $pickupNotes }}</p>
                        @endif
                    </div>
                @endif
            @elseif(strtolower($status) === 'resolved')
                <p>Your item has been successfully claimed. Thank you for using FindMe@CCIS!</p>
            @else
                <p>Unfortunately your claim was not verified. You may submit a new claim with better proof of ownership.</p>
            @endif
            <p style="margin-top: 30px;">Thank you,<br><strong>FindMe@CCIS Admin</strong></p>
        </div>
        <div class="footer">
            Lost and Found Management System 2025. This is an automated email.
        </div>
    </div>
</body>
</html>
