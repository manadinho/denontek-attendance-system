<section class="space-y-6">

    <x-dark-button
        x-data=""
        x-on:click.prevent="resetStaffModalForm();$dispatch('open-modal', 'timetable-create-edit-modal')">+Add</x-dark-button>

    <x-modal name="timetable-create-edit-modal" id="timetable-create-edit-modal" focusable>
        <form method="post" id="timetable-form" action="{{ route('staf-time-manage.timetables.store') }}" class="p-6">
            @csrf
            <input type="hidden" name="id" id="id">

            <h2 class="text-lg font-medium text-gray-900 text-start">
                {{ __('Create Timetable') }}
            </h2>

            <div class="mt-6 text-start">

                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control rounded" required>
                </div>
                <div class="form-group">
                    <label for="late_time">Late Time (Minutes)</label>
                    <input id="late_time" class="form-control rounded" type="number" name="late_time" value="" autofocus>
                </div>
                <div class="form-group">
                    <label for="leave_early_time">Leave Early Time (Minutes)</label>
                    <input id="leave_early_time" class="form-control rounded" type="number" name="leave_early_time" value="" autofocus>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="on_time">On Time</label>
                            <input id="on_time" class="form-control rounded" type="time" name="on_time" value="" autofocus>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="off_time">Off Time</label>
                            <input id="off_time" class="form-control rounded" type="time" name="off_time" value="" autofocus>
                        </div>
                    </div>
                </div>

                <div class="form-group mt-2">
                    
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
