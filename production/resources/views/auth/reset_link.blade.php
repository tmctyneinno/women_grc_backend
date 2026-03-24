<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Reset Password</title>
<style>
    body {
        margin: 0;
        padding: 0;
        background-color: #1D2548;
        font-family: 'Inter', sans-serif;
    }

    .wrapper {
        width: 100%;
        padding: 60px 0;
        background-color: #1D2548;
    }

    .container {
        max-width: 500px;
        margin: 0 auto;
        background-color: #ffffff;
        border-radius: 14px;
        overflow: hidden;
    }

    .header {
        background-color: #D4D7E1;
        text-align: center;
        padding: 25px;
    }

    .header img {
        width: 55px;
        height: auto;
    }

    .content {
        padding: 40px;
        text-align: center;
    }

    h1 {
        font-size: 28px;
        color: #1D2548;
        margin-bottom: 20px;
        font-weight: 700;
    }

    p {
        font-size: 15px;
        color: #111111;
        line-height: 22px;
        margin-bottom: 25px;
    }

    .btn {
        display: inline-block;
        background-color: #B03436;
        color: #ffffff;
        text-decoration: none;
        padding: 12px 28px;
        border-radius: 10px;
        font-weight: 600;
        margin-top: 10px;
    }

    .footer {
        padding: 20px;
        font-size: 12px;
        color: #878787;
        text-align: center;
        background-color: #f5f5f5;
    }
</style>
</head>

<body>
<div class="wrapper">
    <div class="container">

        <div class="header">
            <img src="{{ asset('assets/media/photos/logo.png') }}" alt="WGRFC Logo">
        </div>

        <div class="content">
            <h1>Forgot your password?</h1>

            <p>
                Hi {{ $user->first_name ?? 'there' }},
            </p>

            <p>
                We received a request to reset your password for your WGRFC account.
                Click the button below to reset it.
                This link will expire in <strong>60 minutes</strong>.
            </p>

            <a href="{{ $url }}" class="btn">
            
                Reset Your Password
            </a>

            <!-- <form action="{{ $url }}" method="POST" style="margin-top: 20px;">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <input type="hidden" name="email" value="{{ $user->email }}">
                <button type="submit" class="btn">
                    Reset Your Password
                </button>
            </form> -->

            

            <p style="margin-top:30px; font-size:13px;">
                If you did not request this password reset, you can safely ignore this email.
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} WGRFC. All rights reserved.
        </div>

    </div>
</div>
</body>
</html>
