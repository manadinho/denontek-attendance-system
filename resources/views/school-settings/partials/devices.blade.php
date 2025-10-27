<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Devices') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your Devices.") }}
        </p>
    </header>

    <div class="table-responsive mt-3">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>MAC Address</th>
                    <th>Date Added</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($devices as $device)
                    <tr>
                        <td>
                            <input type="text" style="background: transparent; border-radius:9px;width: 90%" value="{{ $device->name ? $device->name:"Attendace".$loop->iteration }}" onchange="updateDeviceName('{{$device->id}}', this)">
                            <svg style="display:none;width:30px;height:30px;margin-left:-33px;margin-bottom:14px" id="device-checkmark-{{$device->id}}" class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg>
                        </td>
                        <td>{{ $device->mac_address }}</td>
                        <td>{{ $device->created_at }}</td>
                        <td style="min-width:70px;">
                            <button class="btn btn-dark device-find-btn" id="find-device-button-{{ $device->mac_address }}" onclick="findDevice('{{ $device->mac_address }}')">Find</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-gray-500 py-4">
                            {{ __("No attendance terminals found.") }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
