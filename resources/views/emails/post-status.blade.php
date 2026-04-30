<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: white; max-width: 500px; margin: auto; border-radius: 10px; padding: 30px; }
        .header { background: #2c7a4b; color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
        .status-approved { color: #2c7a4b; font-weight: bold; }
        .status-rejected { color: #cc0000; font-weight: bold; }
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
            <p>Your report for the item <strong>"{{ $itemName }}"</strong> has been:</p>
            <p class="{{ strtolower($status) === 'approved' ? 'status-approved' : 'status-rejected' }}" style="font-size: 1.3em;">
                {{ strtoupper($status) }}
            </p>
            @if($reason)
                <p><strong>Reason:</strong> {{ $reason }}</p>
            @endif
            <p>You can view your posts and claims by logging into the system.</p>
            <p style="margin-top: 30px;">Thank you,<br><strong>FindMe@CCIS Admin</strong></p>
        </div>
        <div class="footer">
            Lost and Found Management System 2025. This is an automated email.
        </div>
    </div>
</body>
</html>