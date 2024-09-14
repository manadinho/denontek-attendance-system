<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="mt-3">
        <div class="row">
            @forelse($standards as $standard)
                @include('partials.attendance-card', ['standard' => $standard])
            @empty
                <p>No Standard Found</p>
            @endforelse
        </div>
    </div>
</x-app-layout>