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

            <div class="col-md-6">
                <x-input-label for="checkout_start" :value="__('CheckOut Start')" />
                <x-text-input id="checkout_start" name="checkout_start" type="time" class="mt-1 block w-full" :value="old('checkout_start', $schoolSettings->checkout_start)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('checkout_start')" />
            </div>

            <div class="col-md-6">
                <x-input-label for="checkout_end" :value="__('CheckOut End')" />
                <x-text-input id="checkout_end" name="checkout_end" type="time" class="mt-1 block w-full" :value="old('checkout_end', $schoolSettings->checkout_end)" required autofocus />
                <x-input-error class="mt-2" :messages="$errors->get('checkout_end')" />
            </div>
            <x-input-label for="checkin_end" :value="__('Week Days')" />
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
    <div class="flex items-center gap-4">
        <x-danger-button id="sync_time_with_device">{{ __('Sync Time With Device') }}</x-danger-button>
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
    </script>
</section>
