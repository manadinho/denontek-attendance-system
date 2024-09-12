<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Staff') }}
        </h2>
    </x-slot>

    <div class="mt-3">
        <div class="text-end">
            @include('staff.partials.staff-modal')
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="20%">Name</th>
                        <th width="20%">Email</th>
                        <th width="20%">Type</th>
                        <th width="25%">Created At</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staffMembers as $staff)
                        <tr>
                            <td>{{ $staff->name }}</td>
                            <td>{{ $staff->email }}</td>
                            <td>{{ $staff->type }}</td>
                            <td>{{ $staff->created_at->format('Y-m-d') }}</td>
                            <td>
                                <a href="javascript:void(0)" title="Edit" onclick="editStaff({{$staff}})"><i class="fas fa-edit"></i></a>
                                <a href="javascript:void(0)" title="Delete" onclick="deleteStaff('{{route('staff.destroy', $staff->id)}}')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">No Staff Member Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $staffMembers->links() }}
        </div>
        <script>
            function resetStaffModalForm() {
                $('#id').val("")
                $('#name').val("");
                $('#email').val("");
                $('#type').val("teacher");
                $('#rfid').val("");
            }

            function editStaff(staff) {
                console.log(staff);
                $('#id').val(staff.id)
                $('#name').val(staff.name);
                $('#email').val(staff.email);
                $('#type').val(staff.type);
                $('#rfid').val(staff.rfid);
                
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'staff-create-edit-modal' }));
            }

            function deleteStaff(url) {
                Swal.fire({
                    title: "Do you really want to delete Staff Member?",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: "Delete"
                    }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            }
            ws.onmessage = (event) => {
                const message = JSON.parse(event.data);
                console.log('Received message from server:', message);
                
                // Handle different message types if needed
                if (message.type === 'uuid') {
                    console.log('Received UUID:', message.data);
                }
                if (message.type === 'disconnect') {
                    console.log('Server disconnected:', message.message);
                }
                
                if(message.type === 'register') {
                    $('#rfid').val(message.rfid);
                }
            };
        </script>        
    </div>
</x-app-layout>
