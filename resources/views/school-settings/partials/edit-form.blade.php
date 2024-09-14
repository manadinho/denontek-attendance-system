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
</section>
