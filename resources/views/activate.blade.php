<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ trans('license-manager::license.activate') }}</title>
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
            margin-bottom: 20px;
            text-align: center;
        }

        p {
            margin-bottom: 30px;
            text-align: center;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 10px 15px;
            font-size: 16px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            box-sizing: border-box;
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

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>{{ trans('license-manager::license.form_title') }}</h1>
        <p>{{ trans('license-manager::license.form_description') }}</p>

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('license.activate') }}" method="POST">
            @csrf
            <div class="form-group">
                <input type="text" name="license_key" class="form-control"
                    placeholder="{{ trans('license-manager::license.form_key_placeholder') }}"
                    value="{{ old('license_key') }}" required>
            </div>

            <div class="button-group">
                <button type="submit" class="button button-primary">
                    {{ trans('license-manager::license.submit') }}
                </button>

                <a href="{{ route('license.status') }}" class="button button-secondary">
                    {{ trans('license-manager::license.back') }}
                </a>
            </div>
        </form>
    </div>
</body>

</html>
