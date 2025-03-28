<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('license-manager::license.title') }}</title>
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
        }

        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 14px;
        }

        .status-valid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-invalid {
            background-color: #f8d7da;
            color: #721c24;
        }

        .info-card {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .info-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
        }

        .info-title {
            font-size: 20px;
            font-weight: bold;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
        }

        .info-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #dee2e6;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .label {
            font-weight: bold;
            width: 30%;
        }

        .button-group {
            margin-top: 30px;
            text-align: center;
        }

        .button {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 4px;
            font-weight: bold;
            text-decoration: none;
            margin: 0 5px;
            cursor: pointer;
        }

        .button-primary {
            background-color: #007bff;
            color: white;
            border: none;
        }

        .button-danger {
            background-color: #dc3545;
            color: white;
            border: none;
        }

        .button-secondary {
            background-color: #6c757d;
            color: white;
            border: none;
        }

        .alert {
            padding: 12px 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>{{ trans('license-manager::license.title') }}</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <div class="info-card">
            <div class="info-header">
                <div class="info-title">{{ trans('license-manager::license.status') }}</div>
                <div class="status-badge {{ $isValid ? 'status-valid' : 'status-invalid' }}">
                    {{ $isValid ? trans('license-manager::license.valid') : trans('license-manager::license.invalid') }}
                </div>
            </div>

            @if ($licenseKey)
                <table class="info-table">
                    <tr>
                        <td class="label">{{ trans('license-manager::license.key') }}</td>
                        <td>{{ $licenseKey }}</td>
                    </tr>
                    @if ($licenseData)
                        @if (isset($licenseData['domain']))
                            <tr>
                                <td class="label">{{ trans('license-manager::license.domain') }}</td>
                                <td>{{ $licenseData['domain'] }}</td>
                            </tr>
                        @endif
                        @if (isset($licenseData['ip']))
                            <tr>
                                <td class="label">{{ trans('license-manager::license.ip') }}</td>
                                <td>{{ $licenseData['ip'] }}</td>
                            </tr>
                        @endif
                        @if (isset($licenseData['expires_at']))
                            <tr>
                                <td class="label">{{ trans('license-manager::license.expires_at') }}</td>
                                <td>{{ $licenseData['expires_at'] }}</td>
                            </tr>
                        @endif
                        @if (isset($licenseData['activated_at']))
                            <tr>
                                <td class="label">{{ trans('license-manager::license.activated_at') }}</td>
                                <td>{{ $licenseData['activated_at'] }}</td>
                            </tr>
                        @endif
                        @if (isset($licenseData['last_checked']))
                            <tr>
                                <td class="label">{{ trans('license-manager::license.last_checked_at') }}</td>
                                <td>{{ $licenseData['last_checked'] }}</td>
                            </tr>
                        @endif
                    @endif
                </table>
            @else
                <p>{{ trans('license-manager::license.form_description') }}</p>
            @endif
        </div>

        <div class="button-group">
            @if ($isValid)
                <form action="{{ route('license.deactivate') }}" method="POST" style="display: inline-block;">
                    @csrf
                    <button type="submit" class="button button-danger"
                        onclick="return confirm('Are you sure you want to deactivate this license?')">
                        {{ trans('license-manager::license.deactivate') }}
                    </button>
                </form>
                <button class="button button-secondary" onclick="window.location.reload()">
                    {{ trans('license-manager::license.verify') }}
                </button>
            @else
                <a href="{{ route('license.activate.form') }}" class="button button-primary">
                    {{ trans('license-manager::license.activate') }}
                </a>
            @endif
        </div>
    </div>
</body>

</html>
