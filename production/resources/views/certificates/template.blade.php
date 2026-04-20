<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificate - {{ $certificate->certificate_code }}</title>
    <style>
        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f7ff;
        }
        .canvas {
            max-width: 1000px;
            margin: 30px auto;
            background: #fff;
            border: 10px solid #293567;
            padding: 40px 50px;
            text-align: center;
        }
        .logo {
            width: 110px;
            margin: 0 auto 10px auto;
            display: block;
        }
        .kicker {
            color: #b03436;
            font-size: 14px;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 10px;
            font-weight: 700;
        }
        .title {
            font-size: 44px;
            color: #293567;
            margin: 10px 0;
            font-weight: 800;
        }
        .line {
            width: 120px;
            margin: 18px auto;
            border-top: 3px solid #b03436;
        }
        .subtitle {
            font-size: 16px;
            color: #5b6383;
            margin-bottom: 8px;
        }
        .name {
            font-size: 50px;
            /* color: #1f2d5d; */
            color: #000,
            font-weight: 200;
            margin: 14px 0;
        }
        .course {
            font-size: 22px;
            color: #283a72;
            font-weight: 700;
            margin: 14px 0;
        }
        .meta {
            margin-top: 25px;
            color: #475075;
            font-size: 14px;
        }
        .barcode {
            margin: 16px auto 0 auto;
            width: 210px;
            padding: 8px 8px 4px 8px;
            border: 1px solid #d8dff2;
            border-radius: 8px;
            background: #fff;
        }
        .verify-link {
            margin-top: 8px;
            color: #54608b;
            font-size: 11px;
            word-break: break-all;
        }
        .footer {
            margin-top: 26px;
            font-size: 12px;
            color: #6c7495;
        }
    </style>
</head>
<body>
    <div class="canvas">
        @if(!empty($logoDataUri))
            <img class="logo" src="{{ $logoDataUri }}" alt="WGRCFP Logo">
        @endif
        <div class="kicker">WGRCFP Learning Center</div>
        <div class="title">Certificate of Completion</div>
        <div class="line"></div>

        <div class="subtitle">This certifies that</div>
        <div class="name"><i>{{ $learnerName }}</i></div>
        <div class="subtitle">has successfully completed the course</div>
        <div class="course">{{ $courseTitle }}</div>

        <div class="meta">
            Completion Date: <strong>{{ $completionDate }}</strong><br>
            Certificate ID: <strong>{{ $certificate->certificate_code }}</strong><br>
            Verification Code: <strong>{{ $certificate->verification_code }}</strong>
        </div>

        <!--@if(!empty($barcodeSvg))
            <div class="barcode align-center">{!! $barcodeSvg !!}</div>
        @endif-->
        @if(!empty($verificationUrl))
            <div class="verify-link">Verify: {{ $verificationUrl }}</div>
        @endif

        <div class="footer">
            Women in Governance, Risk and Compliance & Financial Crime Prevention
        </div>
    </div>
</body>
</html>
