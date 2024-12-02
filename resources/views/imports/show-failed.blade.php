<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Import Fails') }}
        </h2>
    </x-slot>

    <div class="mt-3">
        @if($uploadFails->count())
            <div class="table-responsive mt-3">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            @forelse($columns as $column)
                                <th>{{$column}}</th>
                            @empty
                            @endforelse
                            <th>errors</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($uploadFails as $uploadFail)
                            <tr>
                                @forelse($columns as $column)
                                    <td>{{ $uploadFail->data[$column] ?? '' }}</td>
                                @empty
                                @endforelse
                                <td>
                                    @forelse($uploadFail->validation_errors as $error)
                                        {{ $error }}<br>
                                    @empty
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">No Files Uploaded Yet</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @else
            <h5 class="text-center">There are no Fails</h5>
        @endif
    </div>
</x-app-layout>