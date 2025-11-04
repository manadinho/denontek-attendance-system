{{-- resources/views/schools/create.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 m-0">Create New School</h2>
    </x-slot>

    <div class="container py-4">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Fix the following:</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('setup-school.store') }}" class="needs-validation" novalidate>
            @csrf

            <div class="card mb-4">
                <div class="card-header fw-semibold">School</div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">School Name</label>
                        <input type="text" name="school[name]" value="{{ old('school.name') }}" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Address</label>
                        <input type="text" name="school[address]" value="{{ old('school.address') }}" class="form-control" required>
                    </div>
                    <div class="col-12">
                        <small class="text-muted">Channel ID will be auto-generated (RANDOM-:id).</small>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header fw-semibold">School Settings</div>
                <div class="card-body row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Check-in Start (HH:MM)</label>
                        <input type="time" name="settings[checkin_start]" value="{{ old('settings.checkin_start','07:00') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Check-in End</label>
                        <input type="time" name="settings[checkin_end]" value="{{ old('settings.checkin_end','09:00') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Check-out Start</label>
                        <input type="time" name="settings[checkout_start]" value="{{ old('settings.checkout_start','11:00') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Check-out End</label>
                        <input type="time" name="settings[checkout_end]" value="{{ old('settings.checkout_end','13:00') }}" class="form-control" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Buffer Minutes</label>
                        <input type="number" name="settings[buffer_minutes]" min="0" value="{{ old('settings.buffer_minutes','0') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Check-in Sync Time</label>
                        <input type="time" name="settings[checkin_sync_time]" value="{{ old('settings.checkin_sync_time','10:00') }}" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Check-out Sync Time</label>
                        <input type="time" name="settings[checkout_sync_time]" value="{{ old('settings.checkout_sync_time','14:00') }}" class="form-control" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6">
                    <div class="card mb-4 h-100">
                        <div class="card-header fw-semibold">Owner</div>
                        <div class="card-body row g-3">
                            <div class="col-12">
                                <label class="form-label">Name</label>
                                <input type="text" name="owner[name]" value="{{ old('owner.name') }}" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" name="owner[email]" value="{{ old('owner.email') }}" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Password</label>
                                <input type="password" name="owner[password]" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <small class="text-muted">Owner will be linked via <code>owner_school</code> pivot.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="card mb-4 h-100">
                        <div class="card-header fw-semibold">School Admin User</div>
                        <div class="card-body row g-3">
                            <div class="col-12">
                                <label class="form-label">Name</label>
                                <input type="text" name="admin[name]" value="{{ old('admin.name','School Admin') }}" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Email</label>
                                <input type="email" name="admin[email]" value="{{ old('admin.email') }}" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Contact</label>
                                <input type="text" name="admin[contact]" value="{{ old('admin.contact','03001234567') }}" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Password</label>
                                <input type="password" name="admin[password]" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <small class="text-muted"><code>type</code> will be set to <strong>admin</strong>.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header fw-semibold">First Device</div>
                <div class="card-body row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Device Name</label>
                        <input type="text" name="device[name]" value="{{ old('device.name') }}" class="form-control" placeholder="Device 1" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">MAC Address</label>
                        <input type="text" name="device[mac_address]" value="{{ old('device.mac_address') }}" class="form-control" placeholder="48:3F:DA:A6:15:E1" required>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-dark">Create School</button>
            </div>
        </form>
    </div>
</x-app-layout>
