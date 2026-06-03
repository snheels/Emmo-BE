<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f0f4fa;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 560px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        }
        .header {
            background-color: #235CB1;
            padding: 36px 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 28px;
            letter-spacing: 3px;
            font-weight: 700;
        }
        .header p {
            color: rgba(255,255,255,0.75);
            margin: 8px 0 0;
            font-size: 13px;
        }
        .body {
            padding: 36px 40px;
        }
        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 14px;
        }
        .intro {
            color: #555;
            font-size: 14px;
            line-height: 1.8;
            margin-bottom: 28px;
        }
        .credentials-box {
            background-color: #f0f4fa;
            border-radius: 6px;
            padding: 18px 22px;
            margin-bottom: 24px;
        }
        .credentials-box p {
            margin: 6px 0;
            font-size: 14px;
            color: #333;
        }
        .credentials-box span {
            font-weight: 600;
            color: #235CB1;
        }
        .warning {
            background-color: #f7f7f7;
            border-radius: 6px;
            padding: 14px 18px;
            font-size: 13px;
            color: #666;
            margin-bottom: 28px;
        }
        .footer {
            background-color: #235CB1;
            text-align: center;
            padding: 16px;
            font-size: 12px;
            color: rgba(255,255,255,0.6);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>EMMO</h1>
            <p>Project Management & Productivity Platform</p>
        </div>

        <div class="body">
            <p class="greeting">Welcome, {{ $name }}</p>

            <p class="intro">
                Your account has been successfully created. Emmo is a platform where you can manage
                your projects, collaborate with your team, and monitor your productivity all in one place.
            </p>

            <div class="credentials-box">
                <p>Login Credentials</p>
                <p>Email &nbsp;&nbsp;&nbsp;: <span>{{ $email }}</span></p>
                <p>Password : <span>{{ $password }}</span></p>
            </div>

            <div class="warning">
                This password is permanent and cannot be changed. Please keep it safe and do not share it with anyone.
            </div>
        </div>

        <div class="footer">
            © {{ date('Y') }} Emmo. All rights reserved.
        </div>
    </div>
</body>
</html>
