<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Attendance Devices') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your Attendance devices.") }}
        </p>
    </header>

    <div class="table-responsive mt-3">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Chip ID</th>
                    <th>Date Added</th>
                    <th>Date Updated</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendanceDevices as $device)
                    <tr>
                        <td>
                            <input type="text" style="background: transparent; border-radius:9px;width: 90%" value="{{ $device->name ? $device->name:"Attendace".$loop->iteration }}" onchange="updateDeviceName('{{$device->id}}', this)">
                            <svg style="display:none;width:30px;height:30px;margin-left:-33px;margin-bottom:14px" id="device-checkmark-{{$device->id}}" class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/><path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/></svg>
                        </td>
                        <td>{{ $device->chip_id }}</td>
                        <td>{{ $device->created_at }}</td>
                        <td>{{ $device->updated_at }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-gray-500 py-4">
                            {{ __("No attendance devices found.") }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
