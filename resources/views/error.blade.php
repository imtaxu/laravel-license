<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('license-manager::license.error_title') }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        .container {
            width: 100%;
            max-width: 640px;
            padding: 20px 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .error-icon {
            color: #dc3545;
            font-size: 64px;
            margin-bottom: 20px;
        }

        h1 {
            color: #dc3545;
            margin-bottom: 20px;
        }

        p {
            margin-bottom: 20px;
        }

        .info-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            padding: 15px;
            margin-top: 30px;
            text-align: left;
        }

        .info-title {
            font-weight: bold;
            margin-bottom: 10px;
        }

        .info-item {
            margin-bottom: 8px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="error-icon">⚠️</div>
        <h1>{{ trans('license-manager::license.error_title') }}</h1>
        <p>{{ trans('license-manager::license.error_message') }}</p>

        <div class="info-box">
            <div class="info-title">{{ trans('license-manager::license.error_contact') }}</div>
            <div class="info-item"><strong>{{ trans('license-manager::license.domain') }}:</strong>
                {{ request()->getHost() }}</div>
            <div class="info-item"><strong>{{ trans('license-manager::license.ip') }}:</strong> {{ request()->ip() }}
            </div>
            <div class="info-item"><strong>{{ trans('license-manager::license.key') }}:</strong>
                {{ $licenseKey ?? 'Not available' }}</div>
            <div class="info-item"><strong>Date:</strong> {{ now() }}</div>
        </div>
    </div>
</body>

</html>
