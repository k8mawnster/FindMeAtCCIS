<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: white; max-width: 500px; margin: auto; border-radius: 10px; padding: 30px; }
        .header { background: #2c7a4b; color: white; padding: 20px; border-radius: 10px 10px 0 0; text-align: center; }
        .btn { display: inline-block; background: #2c7a4b; color: white; padding: 12px 25px; border-radius: 20px; text-decoration: none; margin-top: 20px; }
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
            <p>We received a request to reset your password. Click the button below to reset it:</p>
            <div style="text-align: center;">
                <a href="{{ url('/reset-password?token=' . $resetToken) }}" class="btn">
                    Reset My Password
                </a>
            </div>
            <p style="margin-top: 20px; font-size: 0.9em; color: #666;">
                This link will expire in 60 minutes. If you did not request a password reset, ignore this email.
            </p>
            <p style="margin-top: 30px;">Thank you,<br><strong>FindMe@CCIS Admin</strong></p>
        </div>
        <div class="footer">
            Lost and Found Management System 2025. This is an automated email.
        </div>
    </div>
</body>
</html>