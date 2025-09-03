<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f5f9;
            margin: 0;
            padding: 0;
        }

        .email-container {
            max-width: 600px;
            margin: 30px auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 30px;
        }

        .header {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 30px;
        }

        .content p {
            font-size: 16px;
            color: #374151;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            background-color: #1f2937;
            color: #ffffff !important;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: bold;
        }

        .footer {
            font-size: 14px;
            color: #6b7280;
            text-align: center;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="email-container">

        <div class="content">
            <p><strong>Hello {{ $userName }}!</strong></p>

            <p>Welcome to {{ $appName }}. Thank you for registering with us.</p>

            <p>Please click the button below to verify your email address and activate your account.</p>

            <p style="text-align: center;">
                <!-- <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a> -->
                <a href="{{ $verificationUrl }}" style="padding: 10px 20px; background-color: #3490dc; color: #fff; text-decoration: none; border-radius: 4px;">
                    Verify Email Address
                </a>
            </p>

            <p>If you did not create an account, no further action is required.</p>

            <p>Thank you for using our application!</p>
        </div>
    </div>
</body>

</html>