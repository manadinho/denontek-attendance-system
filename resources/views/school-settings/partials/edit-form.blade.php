<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@yaireo/tagify/dist/tagify.css">
<script src="https://cdn.jsdelivr.net/npm/@yaireo/tagify"></script>
<style>
    input[type="checkbox"] {
        display:none;
    }
    .weekdays-container {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .weekday {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        border-radius: 5px;
        font-size: 14px;
        cursor: pointer;
        font-weight: 900;
    }

    .weekday input[type="checkbox"] {
        margin-right: 8px;
        transform: scale(1.2); /* Make the checkbox slightly larger */
    }
    .on {
        background-color: #98f398;
        color: green;
    }

    .off {
        background-color: #f39898;
        color: red;
    }

</style>
<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('School Settings') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your school checkin and checkout settings.") }}
        </p>
    </header>

    <form method="post" action="{{ route('school-settings.update') }}" class="mt-6 space-y-6">
        @csrf
        <div class="row">
            <div class="col-md-6">
                <x-input-label for="checkin_start" :value="__('CheckIn Start')" />
                <x-text-input id="checkin_start" name="checkin_start" type="time" class="mt-1 block w-full" :value="old('checkin_start', $schoolSettings->checkin_start)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('checkin_start')" />
            </div>

            <div class="col-md-6">
                <x-input-label for="checkin_end" :value="__('CheckIn End')" />
                <x-text-input id="checkin_end" name="checkin_end" type="time" class="mt-1 block w-full" :value="old('checkin_end', $schoolSettings->checkin_end)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('checkin_end')" />
            </div>

            <div class="col-md-6 pt-2">
                <x-input-label for="checkout_start" :value="__('CheckOut Start')" />
                <x-text-input id="checkout_start" name="checkout_start" type="time" class="mt-1 block w-full" :value="old('checkout_start', $schoolSettings->checkout_start)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('checkout_start')" />
            </div>

            <div class="col-md-6 pt-2">
                <x-input-label for="checkout_end" :value="__('CheckOut End')" />
                <x-text-input id="checkout_end" name="checkout_end" type="time" class="mt-1 block w-full" :value="old('checkout_end', $schoolSettings->checkout_end)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('checkout_end')" />
            </div>
            
            <div class="col-md-6 pt-2">
                <x-input-label for="buffer_minutes" :value="__('Buffer Minutes')" />
                <x-text-input id="buffer_minutes" name="buffer_minutes" type="number" class="mt-1 block w-full" :value="old('buffer_minutes', $schoolSettings->buffer_minutes)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('buffer_minutes')" />
            </div>

            <div class="col-md-6 pt-2">
                <x-input-label for="admin_phone_numbers" :value="__('Admin Contacts')" />
                <x-text-input id="admin_phone_numbers" name="admin_phone_numbers" type="text" class="mt-1 block w-full" :value="old('admin_phone_numbers', $schoolSettings->admin_phone_numbers)" autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('admin_phone_numbers')" />
            </div>

            <div class="col-md-6 pt-2">
                <x-input-label for="checkin_sync_time" :value="__('Checkin Sync')" />
                <select class="form-control" name="checkin_sync_time" id="checkin_sync_time" required>
                    <option value="" {{ $schoolSettings->checkin_sync_time == '' ? 'selected':'' }} disabled>Select Time</option>
                    @for ($h = 0; $h < 24; $h++)
                        @for ($m = 0; $m < 60; $m += 30)
                            @php $t = sprintf('%02d:%02d:%02d', $h, $m, '00'); @endphp
                            <option value="{{ $t }}" {{ $schoolSettings->checkin_sync_time === $t ? 'selected' : '' }}>
                                {{ $t }}
                            </option>
                        @endfor
                    @endfor
                </select>
            </div>

            <div class="col-md-6 pt-2">
                <x-input-label for="checkout_sync_time" :value="__('Checkout Sync')" />
                <select class="form-control" name="checkout_sync_time" id="checkout_sync_time" required>
                    <option value="" {{ $schoolSettings->checkout_sync_time == '' ? 'selected':'' }} disabled>Select Time</option>
                    @for ($h = 0; $h < 24; $h++)
                        @for ($m = 0; $m < 60; $m += 30)
                            @php $t = sprintf('%02d:%02d:%02d', $h, $m, '00'); @endphp
                            <option value="{{ $t }}" {{ $schoolSettings->checkout_sync_time === $t ? 'selected' : '' }}>
                                {{ $t }}
                            </option>
                        @endfor
                    @endfor
                </select>
            </div>

            <x-input-label class="pt-2 pb-2" for="checkin_end" :value="__('Week Days')" />
            <div class="weekdays-container">
            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
    <div class="row">
        <div class="col-md-3">
            <div class="flex items-center gap-4 pt-2">
                <x-danger-button id="sync_time_with_device">{{ __('Sync Time With Device') }}</x-danger-button>
            </div>
        </div>
        @if(userType() == 'superadmin')
            <div class="col-md-3">
                <div class="flex items-center gap-4 pt-2">
                    <!-- <a href="{{route('remove-all-sessions')}}" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150" style="">{{ __('Remove All Sessions') }}</a> -->
                    <x-danger-button onclick="removeAllSessions()">{{ __('Remove All Sessions') }}</x-danger-button>
                </div>
            </div>
        @endif
    </div>
    <script>
        const weekOffDays = @json($weekOffDays);
        console.log(weekOffDays);
        drawWeekDays();
        checkWeekDaysStatus();

        function checkWeekDaysStatus() {
            document.querySelectorAll('.weekday').forEach(label => {
                const checkbox = label.querySelector('input[type="checkbox"]');
                
                if (checkbox.checked) {
                    label.classList.add('off');
                    label.classList.remove('on');
                } else {
                    label.classList.add('on');
                    label.classList.remove('off');
                }
            })
        }

        function drawWeekDays() {
            const weekdays = ['SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY'];
            const weekdaysContainer = document.querySelector('.weekdays-container');
            weekdays.forEach((weekday, index) => {
                const label = document.createElement('label');
                label.classList.add('weekday', 'on');
                label.innerHTML = `
                    <input type="checkbox" name="weekdays[]" value="${weekday}">
                    ${weekday}
                `;

                // Check if the weekday is a week off day
                if (weekOffDays.includes(weekday)) {
                    label.querySelector('input[type="checkbox"]').checked = true;
                }
                
                label.addEventListener('click', function() {
                    const checkbox = this.querySelector('input[type="checkbox"]');
                    checkbox.checked = !checkbox.checked;
                    checkWeekDaysStatus();
                });
                weekdaysContainer.appendChild(label);
            });
        }

        function removeAllSessions()
        {
            if(confirm("Are you sure you want to remove all sessions?"))
            {
                window.location.href = "{{route('remove-all-sessions')}}";
            }
        }

        const input  = document.querySelector('#admin_phone_numbers');
        const tagify = new Tagify(input, {
            delimiters: ", ",
            maxTags: 10,
            dropdown: { enabled: 0 },
        });
    </script>
</section>
