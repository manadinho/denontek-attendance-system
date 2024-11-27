<section class="space-y-6">

    <x-dark-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'timetable-create-edit-modal')">+Assign Shift</x-dark-button>

    <x-modal name="timetable-create-edit-modal" id="timetable-create-edit-modal" focusable>
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 text-start">
                {{ __('Assign Shift') }}
            </h2>

            <div class="mt-6 text-start">

                <div class="form-group">
                    <label for="name">Shift</label>
                    <select class="form-control rounded" name="shift" id="shift">
                        <option value="">Select Shift</option>
                        @forelse($shifts as $shift)
                            @php
                                if (is_string($shift->timetables)) {
                                    $shift->timetables = json_decode($shift->timetables, true);
                                }

                                $isTimetableSet = false;
                                foreach($shift->timetables as $day => $timetable) {
                                    if ($timetable) {
                                        $isTimetableSet = true;
                                    }
                                }
                            @endphp
                            @if ($isTimetableSet)
                                <option value="{{ $shift->id }}">{{ $shift->name }}</option>
                            @endif
                        @empty
                            <option value="">No Shift Found</option>
                        @endforelse
                    </select>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="from">From</label>
                            <input type="text" id="from" name="from" class="form-control rounded" autocomplete="off">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="to">To</label>
                            <input type="text" id="to" name="to" class="form-control rounded" autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="form-group mt-2">
                    
                </div>

            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button id="assign-shift-modal-cancel-btn" x-on:click="$dispatch('close')">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-dark-button class="ms-3" onclick="createSchedule()">
                    {{ __('Save') }}
                </x-danger-button>
            </div>
        </div>
    </x-modal>
</section>
