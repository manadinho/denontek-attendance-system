<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('school-settings.partials.edit-form')
            </div>

            {{-- Registeration Devices --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('school-settings.partials.registeration-devices')
            </div>


            {{-- Attendance Devices --}}
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                @include('school-settings.partials.attendance-devices')
            </div>
        </div>
    </div>

    <script>
        function updateDeviceName(deviceId, element) {
            const name = element.value;
            $.ajax({
                url: "{{ route('school-settings.update-device') }}",
                type: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{csrf_token()}}'
                },
                data: {
                    deviceId,
                    name
                },
                success: function(response) {
                    if(response.success) {
                        $(`#device-checkmark-${deviceId}`).css('display', 'inline');
                        setTimeout(function () {
                            $(`#device-checkmark-${deviceId}`).css('display', 'none');
                        }, 2000);
                    }
                },
                error: function(xhr) {
                    console.error("An error occurred:", xhr.responseText);
                }
            });
        }

        const syncTimeWithDevice = document.getElementById('sync_time_with_device');
        syncTimeWithDevice.addEventListener('click', function() {
            const date = new Date();
            const formattedDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')} ${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}:${String(date.getSeconds()).padStart(2, '0')}`;
            window.ws.send(JSON.stringify({ type: 'message', data: `DATESYNC|${formattedDate}` }))
        });
    </script>
    
</x-app-layout>
