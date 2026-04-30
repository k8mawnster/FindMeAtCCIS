<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: white; max-width: 500px; margin: auto; border-radius: 10px; padding: 30px; }
        .header { background: #2c7a4b; color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
        .match-box { background: #f0fff4; border: 1px solid #2c7a4b; border-radius: 8px; padding: 15px; margin: 20px 0; }
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
            <p>Good news! A found item has been approved that may match your lost item report.</p>

            <div class="match-box">
                <p><strong>Your Lost Item:</strong> {{ $lostItemName }}</p>
                <p><strong>Found Item:</strong> {{ $foundItemName }}</p>
                <p><strong>Found Location:</strong> {{ $foundLocation }}</p>
                <p><strong>Date Found:</strong> {{ $foundDate }}</p>
            </div>

            <p>Please log in to <strong>FindMe@CCIS</strong> and check the
                <strong>Search & Item Lists</strong> section to view the item and submit a claim
                if it belongs to you.</p>

            <p style="margin-top: 30px;">Thank you,<br><strong>FindMe@CCIS Admin</strong></p>
        </div>
        <div class="footer">
            Lost and Found Management System 2025. This is an automated email.
        </div>
    </div>
</body>
</html>