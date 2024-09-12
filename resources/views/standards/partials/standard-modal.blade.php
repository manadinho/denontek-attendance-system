<section class="space-y-6">

    <x-dark-button
        x-data=""
        x-on:click.prevent="resetStandardModalForm();$dispatch('open-modal', 'standard-create-edit-modal')">+Add</x-dark-button>

    <x-modal name="standard-create-edit-modal" id="standard-create-edit-modal" focusable>
        <form method="post" action="{{ route('standards.store') }}" class="p-6">
            @csrf
            <input type="hidden" name="id" id="id">

            <h2 class="text-lg font-medium text-gray-900 text-start">
                {{ __('Create New Standard') }}
            </h2>

            <div class="mt-6 text-start">
                <div class="form-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="form-control rounded" required>
                </div>

                <div class="form-group mt-2">
                    <label for="name">Teachers</label>
                    <select name="teachers[]" id="teachers-select" multiple="multiple">
                        @foreach($teachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
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
