<!DOCTYPE html>
<html>

<head>
    <title>Admin Login</title>
    <style>
        /* Container & Card */
        .form-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #F7F7F7;
            padding: 20px;
            Width: 100%;
        }

        .card {
            position: relative;
            width: 100%;
            max-width: 360px;
            background: #FFFFFF;
            border: 1px solid #E5E5E5;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 24px;
            box-sizing: border-box;
            text-align: center;
        }

        /* Logo */
        .logo-wrapper {
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 100px;
            background: #FFF;
            border: 4px solid #FFF;
            border-radius: 50%;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-wrapper img {
            max-width: 94px;
            max-height: 94px;
        }

        /* Labels & Inputs */
        .form-group {
            margin: 16px 0 8px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            color: #333;
            margin-bottom: 6px;
        }

        .form-group input[type="tel"],
        .form-group input[type="text"] {
            width: 100%;
            border: none;
            border-bottom: 1px solid #CCC;
            padding: 8px 4px;
            font-size: 16px;
            outline: none;
            box-sizing: border-box;
        }

        /* OTP boxes */
        .otp-container {
            display: flex;
            justify-content: space-between;
            margin: 12px 0;
            position: relative;
            cursor: text;
        }

        .otp-box {
            width: 48px;
            height: 48px;
            border: none;
            border-bottom: 1px solid #CCC;
            font-size: 24px;
            text-align: center;
            line-height: 48px;
            background: transparent;
        }

        .otp-hidden {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            border: none;
            outline: none;
        }

        /* Links & Buttons */
        .link {
            font-size: 12px;
            color: #116631;
            text-decoration: underline;
            cursor: pointer;
        }

        .actions {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 12px;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }

        .footer a {
            color: #116631;
            text-decoration: underline;
            margin-left: 4px;
        }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="form-wrapper">
        <div class="card">
            <div class="logo-wrapper">
                <img src="{{ asset('storage/logo/logo.png') }}" width="100%" alt="Logo">
            </div>
            
            <form method="POST" action="{{ route('admin.login') }}">
                @csrf

                <div class="form-group">
                    <label>Name</label>
                    <input type="text" name="first_name" class="mt-1 text-xs" required autofocus>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="text" name="contact_number" class="mt-1 text-xs" required>
                </div>
                <br>
                <button type="submit" class="btn" style="background-color: #116631; color: white;width: 100%;">Login</button>
            </form>
        </div>
    </div>
</body>

</html>