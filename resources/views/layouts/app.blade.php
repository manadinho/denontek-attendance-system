<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="current-route" content="{{ Route::currentRouteName() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/x-icon" href="https://denontek.com.pk/image/catalog/Logo/icon.png">

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
            #sync-attendance {
                font-size: 20px; 
                margin-right:20px; 
                cursor:pointer;
                color: #339365;
            }
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
            .success-animation { margin:150px auto;}

.checkmark {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    display: block;
    stroke-width: 2;
    stroke: #4bb71b;
    stroke-miterlimit: 10;
    box-shadow: inset 0px 0px 0px #4bb71b;
    animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
    position:relative;
    top: 5px;
    right: 5px;
   margin: 0 auto;
}
.checkmark__circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: #4bb71b;
    fill: #fff;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
 
}

.checkmark__check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
    100% {
        stroke-dashoffset: 0;
    }
}

@keyframes scale {
    0%, 100% {
        transform: none;
    }

    50% {
        transform: scale3d(1.1, 1.1, 1);
    }
}

@keyframes fill {
    100% {
        box-shadow: inset 0px 0px 0px 30px #4bb71b;
    }
}
        </style>
    </head>
    <body class="font-sans antialiased">
        @php
            $device = \App\Models\Device::where('school_id', session('school_id'))->where('type', 'push_to_server')->first();
        @endphp
        @if($device)
            <script>
                
                window.CURRENT_ROUTE_NAME = document.querySelector('meta[name="current-route"]').getAttribute('content');
                if(window.CURRENT_ROUTE_NAME === 'dashboard') {
                    fetchStandardAttendanceCards();
                }
                
                window.ATTENDANCE = [];
                ws = new WebSocket('{{ env("WEBSOCKET_URL") }}/{{str_replace(":", "-", $device->chip_id)}}');
                const pingInterval = 25000;
                let pingIntervalId;

                ws.onopen = () => {
                    console.log('Connected to the WebSocket server');

                    if(window.CURRENT_ROUTE_NAME === 'dashboard') {
                        // Example of sending a message to the server
                        ws.send(JSON.stringify({ type: 'message', data: 'ARP' }));
                    }

                    setInterval(() => {
                        if(window.CURRENT_ROUTE_NAME === 'dashboard') {
                            // Example of sending a message to the server
                            ws.send(JSON.stringify({ type: 'message', data: 'ARP' }));
                        }
                    }, 300000); // 300000 milliseconds = 5 minutes

                    // Start polling to keep the connection alive
                    pingIntervalId = setInterval(() => {
                        if (ws.readyState === WebSocket.OPEN) {
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


                $(document).ready(function() {
                    window.selectedRegistrationDevice = localStorage.getItem('selectedRegistrationDevice') || ''; 
                    $('#registration-device-select').val(window.selectedRegistrationDevice);
                });

                function selectRegistrationDevice(device) {
                    window.selectedRegistrationDevice = $(device).val();
                    localStorage.setItem('selectedRegistrationDevice', window.selectedRegistrationDevice);
                }
                
                ws.onmessage = (event) => {
                    const message = JSON.parse(event.data);
                    
                    if (message.type === 'disconnect') {
                        console.log('Server disconnected:', message.message);
                    }
                    
                    if(message.type === 'register') {
                        const messageValue = message.value;
                        console.log(window.selectedRegistrationDevice, messageValue.split('|')[1], messageValue.split('|')[1] == window.selectedRegistrationDevice)
                        if(messageValue.split('|')[1] == window.selectedRegistrationDevice) {
                            $('#rfid').val(messageValue.split('|')[0]);
                        }
                    }

                    if(message.type === 'status') {
                        $(`#device-chip-${message.value} .circle-online`).removeClass('hidden');
                        $(`#device-chip-${message.value} .circle-offline`).addClass('hidden');
                    }

                    if(message.type === 'onGetAttendance') {

                        // to make sure only the sync requested person listens to the response
                        const element = document.getElementById('sync-attendance');
                        if(!element.classList.contains('fa-spin')) {;
                            return;
                        }

                        // check if valid json string or not
                        try {
                            const attendance = JSON.parse(message.value);

                            // get highest id from the attendance
                            const highestId = Math.max.apply(Math, attendance.map(function(o) { return o.id; }));

                            if(attendance.length > 0) {
                                window.ATTENDANCE = [...window.ATTENDANCE, ...attendance];
                                ws.send(JSON.stringify({ type: 'message', data: `GET_ATTENDANCE|${highestId}` }));
                            }else {
                                if(window.ATTENDANCE.length > 0) {
                                    saveAttendance();
                                }
                            }
                        } catch (e) {
                            console.log(window.ATTENDANCE);
                            if(ws.readyState === WebSocket.OPEN) {
                                saveAttendance();
                            }
                            return;
                        }
                    }
                }

                function syncAttendanceWithDevice() {
                    const element = document.getElementById('sync-attendance');
                    
                    // first check if sync is already in progress or not
                    if(element.classList.contains('fa-spin')) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Attendance is already being synced!',
                        });
                        return;
                    }

                    $.ajax({
                        url: "{{ route('device.get-last-att-id') }}",
                        type: 'get',
                        success: function(response) {
                            const lastId = response.id;
                            // first check if websocket is connected or not
                            if(ws.readyState !== WebSocket.OPEN) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Device is not connected to the server!',
                                });
                                return;
                            }
                            
                            element.classList.add('fa-spin');
                            
                            ws.send(JSON.stringify({ type: 'message', data: `GET_ATTENDANCE|${lastId}` }));
                        },
                        error: function(error) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: 'An error occurred while fetching the last attendance id!',
                            });
                        }
                    });
                }

                function saveAttendance() {
                    if(ATTENDANCE.length > 0) {
                        $.ajax({
                            url: "{{ route('device.mark-attendance-bulk') }}",
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                attendance: window.ATTENDANCE
                            },
                            success: function(response) {
                                if(window.CURRENT_ROUTE_NAME === 'dashboard') {
                                    fetchStandardAttendanceCards();
                                }
                                window.ATTENDANCE = [];

                                // remove the spinner
                                const element = document.getElementById('sync-attendance');
                                element.classList.remove('fa-spin');

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: 'Attendance has been synced successfully!',
                                });
                            },
                            error: function(error) {
                                console.log(error);
                            }
                        });
                    } else{
                        // remove the spinner
                        const element = document.getElementById('sync-attendance');
                        element.classList.remove('fa-spin');

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: 'No new attendance to sync!',
                        });
                    }
                }

                function fetchStandardAttendanceCards() {
                    $.ajax({
                        url: "{{ route('standards-with-attendance') }}",
                        type: 'GET',
                        success: function(response) {
                            $('#standard-attendance-cards').html(response.cards);
                        },
                        error: function(error) {
                            console.log(error);
                        }
                    });
                }
            </script>
        @endif
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
    </body>
</html>
