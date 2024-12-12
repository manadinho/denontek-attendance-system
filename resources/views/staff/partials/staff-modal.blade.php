<section class="space-y-6">

    <x-dark-button
        x-data=""
        x-on:click.prevent="resetStaffModalForm();$dispatch('open-modal', 'staff-create-edit-modal')">+Add</x-dark-button>

    <x-modal name="staff-create-edit-modal" id="staff-create-edit-modal" focusable>
        <form method="post" action="{{ route('staff.store') }}" class="p-6">
            @csrf
            <input type="hidden" name="id" id="id">

            <h2 class="text-lg font-medium text-gray-900 text-start">
                {{ __('Create New Staff Member') }}
            </h2>

            <div class="mt-6 text-start">
                <div class="form-group">
                    <label for="name">Staff Type</label>
                    <select name="type" id="type" class="form-control rounded" required>
                        <option value="teacher">Teacher</option>
                        <option value="employee">Employee</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control rounded" required>
                </div>

                <div class="form-group mt-2">
                    <label for="email">Email</label>
                    <input type="email" name="email" id="email" class="form-control rounded" required>
                </div>

                <div class="form-group mt-2">
                    <label for="password">Password</label>
                    <input type="text" name="password" id="password" class="form-control rounded">
                </div>

                <div class="form-group mt-2">
                    <label for="registration-device-select">Registeration Device</label>
                    <select id="registration-device-select" class="form-control rounded" required onchange="selectRegistrationDevice(this)">
                        <option value="">Select Device</option>
                        @foreach($registrationDevices as $device)
                            <option value="{{ str_replace(':', '-', $device->chip_id) }}">{{ $device->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-2">
                    <label for="rfid">RFID</label>
                    <input type="text" name="rfid" id="rfid" class="form-control rounded" required>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-dark-button class="ms-3">
                    {{ __('Save') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
