<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Timetables') }}
        </h2>
    </x-slot>

    <div class="mt-3">
        <div class="text-end">
            @include('time-manage.partials.timetable-modal')
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="20%">Name</th>
                        <th width="20%">ON Time</th>
                        <th width="20%">OFF Time</th>
                        <th width="25%">Late Time (Minutes)</th>
                        <th width="25%">Leave Early Time (Minutes)</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($timetables as $timetable)
                    <tr>
                        <td>{{ $timetable->name }}</td>
                        <td>{{ $timetable->on_time }}</td>
                        <td>{{ $timetable->off_time }}</td>
                        <td>{{ $timetable->late_time }}</td>
                        <td>{{ $timetable->leave_early_time }}</td>
                        <td>
                            <a href="javascript:void(0)" title="Edit" onclick="editTimetable({{$timetable}})"><i class="fas fa-edit"></i></a>
                            <a href="javascript:void(0)" title="Delete" onclick="deleteTimetable('{{route('staf-time-manage.timetables.destroy', $timetable->id)}}')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="6">No Timetable Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $timetables->links() }}
        </div>
        <script>
            function resetStaffModalForm() {
                // reset form
                $('#timetable-form')[0].reset();
            }

            function editTimetable(timetable) {
                console.log(timetable);
                $('#id').val(timetable.id)
                $('#name').val(timetable.name);
                $('#late_time').val(timetable.late_time);
                $('#leave_early_time').val(timetable.leave_early_time);
                $('#on_time').val(timetable.on_time);
                $('#off_time').val(timetable.off_time);
                $('#checkin_start').val(timetable.checkin_start);
                $('#checkin_end').val(timetable.checkin_end);
                $('#checkout_start').val(timetable.checkout_start);
                $('#checkout_end').val(timetable.checkout_end);
                
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'timetable-create-edit-modal' }));
            }

            function deleteTimetable(url) {
                Swal.fire({
                    title: "Do you really want to delete Timetable?",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: "Delete"
                    }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            }
        </script>        
    </div>
</x-app-layout>
