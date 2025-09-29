<x-app-layout>
    <x-slot name="header">
         <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Attendance Message Templates') }}
        </h2>
    </x-slot>

    @if (session('ok'))
        <div class="mb-4 rounded border border-green-200 bg-green-50 p-3 text-green-800">
            {{ session('ok') }}
        </div>
    @endif

    <div class="mb-4 flex items-center justify-between mt-4">
        <div class="text-sm text-gray-600">
            <!-- Manage custom messages for arrival & departure. -->
        </div>
        <a href="{{ route('attendance-templates.create') }}"
           class="btn btn-dark">
            + New Template
        </a>
    </div>

    <div class="table-responsive mt-3">
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Active</th>
                    <th class="px-4 py-3">Updated</th>
                    <th class="px-4 py-3"></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($templates as $t)
                    <tr>
                        <td class="px-4 py-3 capitalize">{{ $t->type }}</td>
                        <td class="px-4 py-3">
                            @if($t->is_active)
                                <span class="rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800">Active</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2 py-1 text-xs text-gray-600">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $t->updated_at->diffForHumans() }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <a href="{{ route('attendance-templates.edit', $t) }}" class="pr-2"><i class="fas fa-edit"></i></a>
                                <!-- <a href="{{ route('attendance-templates.edit', $t) }}"
                                   class="rounded border px-2 py-1 text-xs hover:bg-gray-50">Edit</a> -->

                                <form method="POST" action="{{ route('attendance-templates.destroy', $t) }}"
                                      onsubmit="return confirm('Delete this template?')">
                                    @csrf @method('DELETE')
                                    <button>
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No templates yet. Click “New Template”.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $templates->links() }}
    </div>
</x-app-layout>
