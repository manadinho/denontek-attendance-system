<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Standards') }}
        </h2>
    </x-slot>

    <div class="mt-3">
        <div class="text-end">
            @include('standards.partials.standard-modal')
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="22%">Name</th>
                        <th width="22%">Teachers</th>
                        <th width="22%">Students Count</th>
                        <th width="24%">Created At</th>
                        <th width="10%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($standards as $standard)
                        <tr>
                            <td>{{ $standard->name }}</td>
                            <td>
                                @forelse($standard->teachers as $teacher)
                                    <span class="badge bg-secondary">{{ $teacher->name }}</span>
                                @empty
                                @endforelse
                            </td>
                            <td>{{$standard->students_count}}</td>
                            <td>{{ $standard->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="javascript:void(0)" onclick="editStandard({{$standard}})"><i class="fas fa-edit"></i></a>
                                <a href="javascript:void(0)" onclick="deleteStandard('{{route('standards.destroy', $standard->id)}}')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No Standards Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $standards->links() }}
        </div>
        <script>
            function resetStandardModalForm() {
                $('#id').val("")
                $('#name').val("");
                $('#teachers-select').val(null).trigger('change');
            }

            function editStandard(standard) {
                console.log(standard);
                $('#id').val(standard.id)
                $('#name').val(standard.name);
                const teacherIds = standard.teachers.map(teacher => teacher.id);
                $('#teachers-select').val(teacherIds).trigger('change');
                
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'standard-create-edit-modal' }));
            }

            function deleteStandard(url) {
                Swal.fire({
                    title: "Do you really want to delete Standard?",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: "Delete"
                    }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            }
            $(document).ready(function() {
                $('#teachers-select').select2();
            });
        </script>        
    </div>
</x-app-layout>
