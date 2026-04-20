<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New User Registration!</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #CECECE;
            color: #424040;
        }
        .container {
            max-width: 400px;
            margin: 50px auto;
            background: #ffffff;
            border-radius: 20px;
            padding: 50px 40px 40px;
            text-align: center;
        }
        .logo img {
            width: 60px;
            height: auto;
        }
        h1 {
            font-size: 24px;
            font-weight: 600;
            color: #111111;
            margin: 20px 0;
        }
        p {
            font-size: 15px;
            line-height: 22px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            background-color: #323D6D;
            color: #b03436;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 700;
            margin: 20px 0;
        }
        .btn-2{
            display: inline-block;
            background-color: #b03436;
            color: #fff;
            text-decoration: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 700;
            margin: 20px 0;
        }
        .footer {
            background-color: #F2EFF3;
            padding: 20px 30px;
            border-radius: 8px;
            font-size: 12px;
            color: #84828E;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <img src="{{ asset('assets/media/photos/logo.png') }}" alt="Logo">
            <!-- <img src="{{ asset('assets/media/favicons/favicon-192x192.png') }}" alt="Logo"> -->
            
        </div>

        <h1>Hello Admin, &#x1F600;</h1>

        

        <p>A new user has registered on WGRCFP.</p>

        <ul>
            <li><strong>Name:</strong> {{ $user->first_name }} {{ $user->last_name }}</li>
            <li><strong>Email:</strong> {{ $user->email }}</li>
            <li><strong>LinkedIn:</strong>
                @if ($linkedinUrl)
                    <a href="{{ $linkedinUrl }}"  class="btn" target="_blank" rel="noopener">Visit Linked in page</a>
                @else
                    N/A
                @endif
            </li>
            <li><strong>Registered At:</strong> {{ optional($user->created_at)->format('Y-m-d H:i') }}</li>
        </ul>
      

        <div class="footer">
            &copy; {{ date('Y') }} WGRFC. All rights reserved.
        </div>
    </div>
</body>
</html>
