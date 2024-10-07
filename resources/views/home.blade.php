<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>School Management System</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <link rel="icon" type="image/x-icon" href="https://denontek.com.pk/image/catalog/Logo/icon.png">

        <style>
            .max-w-7xl{max-width:80rem}
            .mx-auto{margin-left:auto;margin-right:auto}
            .p-6{padding:1.5rem}
            .h-16{height:4rem}
            .w-auto{width:auto}
            .bg-gray-100{--tw-bg-opacity:1;background-color:rgb(243 244 246 / var(--tw-bg-opacity))}
            .mt-16{margin-top:4rem}
            .mt-6{margin-top:1.5rem}
            .text-xl{font-size:1.25rem;line-height:1.75rem}
            .font-semibold{font-weight:600}
            .text-gray-600{--tw-text-opacity:1;color:rgb(75 85 99 / var(--tw-text-opacity))}
            .text-gray-900{--tw-text-opacity:1;color:rgb(17 24 39 / var(--tw-text-opacity))}
            .antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}
            .relative{position:relative}
            .min-h-screen{min-height:100vh}
            .bg-dots-darker{background-image:url("data:image/svg+xml,%3Csvg width='30' height='30' viewBox='0 0 30 30' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1.22676 0C1.91374 0 2.45351 0.539773 2.45351 1.22676C2.45351 1.91374 1.91374 2.45351 1.22676 2.45351C0.539773 2.45351 0 1.91374 0 1.22676C0 0.539773 0.539773 0 1.22676 0Z' fill='rgba(0,0,0,0.07)'/%3E%3C/svg%3E")}
            .bg-center{background-position:center}
            .text-right{text-align:right}
            .z-10{z-index: 10}
            .flex{display:flex}
            .justify-center{justify-content:center}
            .text-center{text-align:center}
            a{color:inherit;text-decoration:inherit}
            html{line-height:1.5;-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;font-family:Figtree, sans-serif;font-feature-settings:normal}body{margin:0;line-height:inherit}
        </style>
    </head>
    <body class="antialiased">
        <div class="relative min-h-screen bg-dots-darker bg-center bg-gray-100">
            @if (Route::has('login'))
                <div class="p-6 text-right z-10">
                    @if(checkAuth())
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600">Log in</a>
                    @endauth
                </div>
            @endif

            <div class="max-w-7xl mx-auto p-6" style="margin-top: 25vh">
                <div class="flex justify-center">
                    <img src="https://denontek.com.pk/image/catalog/new_logo_2.jpg" alt="Denontek Logo">
                </div>

                <div class="mt-16">
                    <p class="mt-6 text-xl font-semibold text-gray-900 text-center">
                        School Attendance System
                    </p>
                </div>

            </div>
        </div>
    </body>
</html>
