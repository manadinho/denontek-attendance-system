<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Schools') }}
        </h2>
    </x-slot>

    <div class="mt-3">
        <div class="row">
            @forelse($schools as $school)
                @include('partials.school-card', ['school' => $school])
            @empty
                <p>No Schools Found</p>
            @endforelse
        </div>
    </div>

</x-app-layout>