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
                        <th width="18%">Messaging</th>
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
                                @if($student->is_on_whatsapp)
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 191.667 191.667" width="1em" height="1em" fill="green" style="vertical-align:middle;"><path d="M95.833,0C42.991,0,0,42.99,0,95.833s42.991,95.834,95.833,95.834s95.833-42.991,95.833-95.834S148.676,0,95.833,0z M150.862,79.646l-60.207,60.207c-2.56,2.56-5.963,3.969-9.583,3.969c-3.62,0-7.023-1.409-9.583-3.969l-30.685-30.685c-2.56-2.56-3.97-5.963-3.97-9.583c0-3.621,1.41-7.024,3.97-9.584c2.559-2.56,5.962-3.97,9.583-3.97c3.62,0,7.024,1.41,9.583,3.971l21.101,21.1l50.623-50.623c2.56-2.56,5.963-3.969,9.583-3.969c3.62,0,7.023,1.409,9.583,3.969C156.146,65.765,156.146,74.362,150.862,79.646z"/></svg>
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 192 192" width="1em" height="1em" fill="red" style="vertical-align:middle;"><path d="M96 0C43 0 0 43 0 96s43 96 96 96 96-43 96-96S149 0 96 0zm39.3 123.3c2.8 2.8 2.8 7.3 0 10.1-1.4 1.4-3.2 2.1-5.1 2.1s-3.7-.7-5.1-2.1L96 106.1l-29.1 29.3c-1.4 1.4-3.2 2.1-5.1 2.1s-3.7-.7-5.1-2.1c-2.8-2.8-2.8-7.3 0-10.1L85.9 96 56.7 66.7c-2.8-2.8-2.8-7.3 0-10.1 2.8-2.8 7.3-2.8 10.1 0L96 85.9l29.1-29.3c2.8-2.8 7.3-2.8 10.1 0 2.8 2.8 2.8 7.3 0 10.1L106.1 96l29.2 29.3z"/></svg>
                                @endif
                             </td>
                            <td>
                                <a href="javascript:void(0)" class="pr-2" onclick="editStudent({{$student}})"><i class="fas fa-edit"></i></a>
                                <a href="javascript:void(0)" onclick="deleteStudent('{{route('students.destroy', $student->id)}}')"><i class="fas fa-trash"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No Students Found</td>
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
