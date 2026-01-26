<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - HQ App</title>
    <link rel="icon" href="{{ asset('logo.png') }}" type="image/png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-teal-400 to-blue-500 h-screen flex items-center justify-center p-4">
    <div id="app" class="w-full max-w-md">
        <login-form></login-form>
    </div>
</body>
</html>
