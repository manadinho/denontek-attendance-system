<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.3.1/css/all.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        {{-- <script src="https://js.pusher.com/3.0/pusher.min.js"></script> --}}

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            a:hover {
                color: inherit !important;
            }
            .select2 {
                width: 100% !important;
            }
            .select2-selection {
                height: 39px !important;
            }
            .w-35 {
                width: 35% !important;
            }
            @media screen and (max-width: 480px) {
                .w-sm-100 {
                    width: 100% !important;
                }
            }
        </style>
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow-sm">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="ps-md-5 pe-md-5 ps-2 pe-2">
                @if ($errors->any())
                    <div class="alert alert-danger mt-2">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show mt-2" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
        <script>
            const ws = new WebSocket('{{ env("WEBSOCKET_URL") }}/123-abc');

            const pingInterval = 25000;
            let pingIntervalId;

            ws.onopen = () => {
                console.log('Connected to the WebSocket server');
                
                // Example of sending a message to the server
                // const message = JSON.stringify({ type: 'message', data: 'Hello, Server!' });
                // ws.send(message);

                // Start polling to keep the connection alive
                pingIntervalId = setInterval(() => {
                    if (ws.readyState === WebSocket.OPEN) {
                        // Send a ping message or an empty message as a heartbeat
                        const pingMessage = JSON.stringify({ type: 'ping' });
                        ws.send(pingMessage);
                    }
                }, pingInterval);
            };

            ws.onclose = () => {
                console.log('WebSocket connection closed');
                // Clear the polling interval when the connection is closed
                clearInterval(pingIntervalId);
            };

            ws.onerror = (error) => {
                console.error('WebSocket error:', error);
                // Clear the polling interval on error
                clearInterval(pingIntervalId);
            };

            // ws.onopen = () => {
            //     console.log('Connected to the WebSocket server');
                
            //     // Example of sending a message to the server
            //     const message = JSON.stringify({ type: 'message', data: 'Hello, Server!' });
            //     ws.send(message);
            // };

            // ws.onmessage = (event) => {
            //     const message = JSON.parse(event.data);
            //     console.log('Received message from server:', message);
                
            //     // Handle different message types if needed
            //     if (message.type === 'uuid') {
            //         console.log('Received UUID:', message.data);
            //     }
            //     if (message.type === 'disconnect') {
            //         console.log('Server disconnected:', message.message);
            //     }
            // };
        </script>
    </body>
</html>
