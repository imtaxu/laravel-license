<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ trans('license::messages.license_invalid') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: #343a40;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            max-width: 600px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 2rem;
            text-align: center;
        }
        .error-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        h1 {
            color: #dc3545;
            margin-bottom: 1rem;
        }
        p {
            font-size: 1.1rem;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }
        .contact-info {
            background-color: #f8f9fa;
            border-radius: 4px;
            padding: 1rem;
            margin-top: 1.5rem;
        }
        .btn {
            display: inline-block;
            background-color: #007bff;
            color: #fff;
            padding: 0.5rem 1.5rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #0069d9;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-icon">&#9888;</div>
        <h1>{{ trans('license::messages.license_invalid') }}</h1>
        <p>{{ trans('license::messages.license_expired_notification_message') }}</p>
        
        <div class="contact-info">
            <p>{{ trans('license::messages.contact_info_text') }}</p>
            <p>{{ trans('license::messages.email') }}: <strong>{{ config('license.contact_email', 'support@example.com') }}</strong></p>
            <p>{{ trans('license::messages.phone') }}: <strong>{{ config('license.contact_phone', '+90 XXX XXX XX XX') }}</strong></p>
        </div>
        
        @if(config('license.renewal_url'))
            <a href="{{ config('license.renewal_url') }}" class="btn">{{ trans('license::messages.renew_license') }}</a>
        @endif
    </div>
</body>
</html>
