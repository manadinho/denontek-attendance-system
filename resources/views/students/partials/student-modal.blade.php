<style>
    input::-webkit-inner-spin-button,
    input::-webkit-outer-spin-button {
        -webkit-appearance: none;
    }
    input[type=number] { -moz-appearance: textfield; }
</style>
<section class="space-y-6">

    <x-dark-button
        class="mt-4 ms-n3"
        x-data=""
        x-on:click.prevent="resetStudentModalForm();$dispatch('open-modal', 'student-create-edit-modal')"><i class="bi bi-person-plus me-2"></i> Add Student</x-dark-button>

    <x-modal name="student-create-edit-modal" id="student-create-edit-modal" focusable>
        <form method="post" action="{{ route('students.store') }}" class="p-6">
            @csrf
            <input type="hidden" name="id" id="id">

            <h2 class="text-lg font-medium text-gray-900 text-start">
                {{ __('Create New Student') }}
            </h2>

            <div class="mt-6 text-start">
                <div class="form-group">
                    <label for="registeration_id">Registeration ID <span class="text-danger">*</span></label>
                    <input type="text" name="registeration_id" id="registeration_id" class="form-control rounded" required>
                </div>

                <div class="form-group">
                    <label for="name">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control rounded" required>
                </div>

                <div class="form-group mt-2">
                    <label for="standard_id">Standard <span class="text-danger">*</span></label>
                    <select name="standard_id" id="standard_id" class="form-control rounded">
                        @forelse($standards as $standard)
                            <option value="{{$standard->id}}">{{$standard->name}}</option>
                        @empty
                        @endforelse
                    </select>
                </div>

                <div class="form-group mt-2">
                    <label for="guardian_name">Guardian Name <span class="text-danger">*</span></label>
                    <input type="text" name="guardian_name" id="guardian_name" class="form-control rounded" required>
                </div>

                <div class="form-group mt-2">
                    <label for="guardian_contact">Guardian Contact <span class="text-danger">*</span></label>
                    <input type="number" name="guardian_contact" id="guardian_contact" class="form-control rounded" required>
                </div>

                <div class="form-group mt-2">
                    <label for="guardian_relation">Guardian Relation <span class="text-danger">*</span></label>
                    <input type="text" name="guardian_relation" id="guardian_relation" class="form-control rounded" required>
                </div>

                <div class="form-group mt-2">
                    <label for="guardian_cnic">Guardian CNIC</label>
                    <input type="text" name="guardian_cnic" id="guardian_cnic" class="form-control rounded">
                </div>

                <div class="form-group mt-2">
                    <label for="registration-device-select">Registeration Device</label>
                    <select id="registration-device-select" class="form-control rounded" onchange="selectRegistrationDevice(this)">
                        <option value="">Select Device</option>
                        @foreach($registrationDevices as $device)
                            <option value="{{ $device->mac_address }}">{{ $device->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mt-2">
                    <label for="rfid">RFID <span class="text-danger">*</span></label>
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
