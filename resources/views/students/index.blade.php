<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Students') }}
        </h2>
    </x-slot>

    <div class="mt-3">
        <div class="row">
            <div class="col-9">
                <div class="text-start">
                    <div class="form-group">
                        <label for=""><b>Standard:</b></label>
                        <select name="" id="" onchange="StandardFilterChanged(this)" class="d-inline w-35 w-sm-100 form-control rounded">
                            <option value="">Select Standard</option>
                            @forelse($standards as $standard)
                                <option value="{{$standard->id}}" {{(request('standard') == $standard->id ? 'selected':'' )}}>{{$standard->name}}</option>
                            @empty
                            @endforelse
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-3">
                <div class="text-end">
                    @include('students.partials.student-modal')
                </div>
            </div>
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="18%">Name</th>
                        <th width="18%">Standard</th>
                        <th width="18%">Guardian Name</th>
                        <th width="18%">Guardian Relation</th>
                        <th width="18%">Guardian Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $student)
                        <tr>
                            <td>{{ $student->name }}</td>
                            <td>{{ $student->standard->name }}</td>
                            <td>{{ $student->guardian_name }}</td>
                            <td>{{ $student->guardian_relation }}</td>
                            <td>{{ $student->guardian_contact }}</td>
                            <td>
                                <a href="javascript:void(0)" onclick="editStudent({{$student}})"><i class="fas fa-edit"></i></a>
                                <a href="javascript:void(0)" onclick="deleteStudent('{{route('students.destroy', $student->id)}}')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">No Students Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $students->links() }}
        </div>


        <script>
            // Pusher.logToConsole = true;

            // var pusher = new Pusher('{{ env('PUSHER_APP_KEY') }}', {
            //     cluster: '{{ env('PUSHER_APP_CLUSTER') }}'
            // });

            // var channel = pusher.subscribe('register-student-{{session("school_id")}}');
            // channel.bind('register-student', function(data) {
            //     $('#rfid').val(data.rfid);
            // });
            // const ws = new WebSocket('{{ env("WEBSOCKET_URL") }}/123-abc');
            // const pingInterval = 25000;
            // let pingIntervalId;

            // ws.onopen = () => {
            //     console.log('Connected to the WebSocket server');
                
            //     // Example of sending a message to the server
            //     // const message = JSON.stringify({ type: 'message', data: 'Hello, Server!' });
            //     // ws.send(message);

            //     // Start polling to keep the connection alive
            //     pingIntervalId = setInterval(() => {
            //         if (ws.readyState === WebSocket.OPEN) {
            //             // Send a ping message or an empty message as a heartbeat
            //             const pingMessage = JSON.stringify({ type: 'ping' });
            //             ws.send(pingMessage);
            //         }
            //     }, pingInterval);
            // };
            // ws.onclose = () => {
            //     console.log('WebSocket connection closed');
            //     // Clear the polling interval when the connection is closed
            //     clearInterval(pingIntervalId);
            // };

            // ws.onerror = (error) => {
            //     console.error('WebSocket error:', error);
            //     // Clear the polling interval on error
            //     clearInterval(pingIntervalId);
            // };
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
            function resetStudentModalForm() {
                $('#id').val("")
                $('#name').val("");
                $('#standard_id').val("");
                $('#guardian_name').val("");
                $('#guardian_contact').val("");
                $('#guardian_relation').val("");
                $('#rfid').val("");
            }

            function editStudent(student) {
                $('#id').val(student.id)
                $('#name').val(student.name);
                $('#standard_id').val(student.standard_id);
                $('#guardian_name').val(student.guardian_name);
                $('#guardian_contact').val(student.guardian_contact);
                $('#guardian_relation').val(student.guardian_relation);
                $('#rfid').val(student.rfid);
                
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'student-create-edit-modal' }));
            }

            function deleteStudent(url) {
                Swal.fire({
                    title: "Do you really want to delete Student?",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: "Delete"
                    }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            }

            function StandardFilterChanged(element) {
                let url = new URL(window.location.href);
                url.searchParams.set('standard', $(element).val());
                window.history.pushState({ path: url.href }, '', url.href);
                window.location.href = url;
            }
        </script>        
    </div>
</x-app-layout>
