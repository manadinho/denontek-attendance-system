<style>
    .shift-card-btn{
        background: #fff;
        color: gray;
        padding: 5px 5px;
        border: solid 1px gray;
        border-radius: 6px;
        margin: 5px;
        cursor: pointer;
    }

    .shift-card-btn:hover{
        background: gray;
        color: #fff;
    }

    .time-table-available {
        background-color: #9add9a !important;
        color: #0c580c !important;
    }

    .time-table-not-available {
        background-color: #f28585 !important;
        color: #630909 !important;
    }
    .timetable-highlight {
        background-color: #ffff99 !important;
    }

    .timetable-red{
        background-color: #ff9999 !important;
    }
</style>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Timetables') }}
        </h2>
    </x-slot>

    <div class="mt-3">
        <div class="text-end">
            @include('employee-schedule.partials.employee-schedule-modal')
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="5%">
                            <input type="checkbox" value="all_checkbox" id="all_checkbox" onclick="checkUncheckAll(this)">
                        </th>
                        <th width="20%">Name</th>
                        <th width="20%">Email</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                    <tr>
                        <td>
                            <input type="checkbox" name="employee_id[]" onclick="checkStateOfAllCheckboxes()" value="{{ $employee->id }}">
                        </td>
                        <td>{{ $employee->name }}</td>
                        <td>{{ $employee->email }}</td>
                        <td id="shift-{{ $loop->index }}"></td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="5">No Employee Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="den-modal den-modal-second" style="visibility: hidden;">
            <div class="den-modal-content modal-large den-modal-content-second mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all">
                <h2 class="text-lg font-medium text-gray-900 text-start">
                    Employee Schedule
                </h2>
                <div id="modal-shift-detail-section"></div>
                <button class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150" onclick="toggleModal('.den-modal-second')">X</button>
            </div>
        </div>
        <script>
            const TIMETABLES = @json($timetables);

            /*  employee shifts showing code started */
            const employees = @json($employees);

            employees.forEach((employee, index) => {
                const shifts = employee.shifts;
                const shiftTd = document.getElementById(`shift-${index}`);
                if (shiftTd) {
                    const shiftUi = drawShifts(shifts);
                    shiftTd.innerHTML = shiftUi
                }
            });
            /*  employee shifts showing code ended */

            /* date piccker code started */
            var dateFormat = "mm/dd/yy",
            from = $( "#from" )
                .datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                numberOfMonths: 3
                })
                .on( "change", function() {
                to.datepicker( "option", "minDate", getDate( this ) );
                }),
            to = $( "#to" ).datepicker({
                defaultDate: "+1w",
                changeMonth: true,
                numberOfMonths: 3
            })
            .on( "change", function() {
                from.datepicker( "option", "maxDate", getDate( this ) );
            });
        
            function getDate( element ) {
                var date;
                try {
                    date = $.datepicker.parseDate( dateFormat, element.value );
                } catch( error ) {
                    date = null;
                }
            
                return date;
            }
            /* date piccker code ended */

            function checkUncheckAll(element) {
                const checkboxes = document.getElementsByName('employee_id[]');
                if(element.checked) {
                    for (let i = 0; i < checkboxes.length; i++) {
                        if (checkboxes[i].type == 'checkbox') {
                            checkboxes[i].checked = true;
                        }
                    }
                } else {
                    for (let i = 0; i < checkboxes.length; i++) {
                        if (checkboxes[i].type == 'checkbox') {
                            checkboxes[i].checked = false;
                        }
                    }
                }
            }

            function checkStateOfAllCheckboxes() {
                const checkboxes = document.getElementsByName('employee_id[]');
                let allChecked = true;
                checkboxes.forEach(checkbox => {
                    if(!checkbox.checked) {
                        document.getElementById('all_checkbox').checked = false;
                        allChecked = false;
                        return;
                    }
                });

                if(allChecked) document.getElementById('all_checkbox').checked = true;
            }

            function createSchedule() {
                const shift_id = document.querySelector('select[name="shift"]').value;
                const from = document.querySelector('input[name="from"]').value;
                const to = document.querySelector('input[name="to"]').value;
                
                if(!shift_id || !from || !to) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please fill all fields!',
                    });
                    return;
                }

                // from and to are valid dates
                if(!from.match(/^\d{2}\/\d{2}\/\d{4}$/) || !to.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please enter valid dates!',
                    });
                    return;
                }

                // from date is before to date
                if(new Date(from) > new Date(to)) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'From date must be before to date!',
                    });
                    return;
                }

                const employees = getSelectedEmployees();
                if(!employees.length) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please select at least one employee!',
                    });
                    return;
                }

                const url = "{{ route('staf-time-manage.employee-schedule.store') }}";

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        shift_id,
                        from,
                        to,
                        employees
                    },
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                            });

                            // reload the page after 1 second
                            setTimeout(() => {
                                location.reload();
                            }, 1000);

                            return;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: response.message,
                        });
                    },
                    error: function(error) {
                        if(error.status === 422) {
                            const errors = error.responseJSON.errors;
                            let message = '';
                            for (const key in errors) {
                                if (errors.hasOwnProperty(key)) {
                                    message += errors[key][0] + '<br>';
                                }
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                html: message,
                            });

                            return;
                        }
                    }
                })
            }

            function getSelectedEmployees() {
                const checkboxes = document.getElementsByName('employee_id[]');
                let users = [];
                checkboxes.forEach(checkbox => {
                    if(checkbox.checked) {
                        users.push(checkbox.value);
                    }
                });

                return users;
            }

            function drawShifts(shifts) {
                if(shifts.length === 0) {
                    return 'No shifts found';
                }

                let content = '<div style="display:flex;">';
                for(const shift of shifts) {
                    content += `<span class="badge bg-secondary" style="width:250px">
                            🕒 &nbsp; ${shift.name}
                            <br>
                            ${formatDate(shift.pivot.start_date).replace(/ - /g, " ")} TO ${formatDate(shift.pivot.end_date)}
                            <br>
                            <span style="display:flex; margin-top: 8px;">
                                <span class="shift-card-btn" onclick='showShiftDetails(${JSON.stringify(shift)})'>
                                    <i class="fas fa-eye"></i>
                                </span>
                                <span class="shift-card-btn" onclick='deleteUserShift(${JSON.stringify(shift)}, this)'>
                                    <i class="fas fa-trash"></i>
                                </span>
                            </span>
                        </span>
                        <br>`;
                }
                content += '</div>';

                return content;
            }

            function showShiftDetails(data) {
                const modal = document.querySelector('.den-modal-second');
                modal.style.visibility = modal.style.visibility == 'visible' ? 'hidden' : 'visible';

                let content = `
                    <h3>Shift Name : <small>${data.name}</small></h3>
                    <h3>Start Date : <small>${formatDate(data.pivot.start_date)}</small></h3>
                    <h3>End Date : <small>${formatDate(data.pivot.end_date)}</small></h3>
                `;

                content += generateViewTimetableContent(data);
                document.querySelector('#modal-shift-detail-section').innerHTML = content;
            }

            function generateViewTimetableContent(shift) {
                let table_body = ``;
                let count = 0;
                for(const day in JSON.parse(shift.timetables)) { 
                    const timetable_id = JSON.parse(shift.timetables)[day];
                    const timetable = TIMETABLES.find(timetable => timetable.id == timetable_id);
                    const start = timetable ? + timetable.on_time.split(':')[0] : null;
                    const end = timetable ? + timetable.off_time.split(':')[0] : null;

                    table_body += `<tr>`;
                    table_body += `<td> ${day}</td>`;
                    for(let i = 0; i < 24; i++) {
                        if(!start) {
                            table_body += `<td class="timetable-red"></td>`;
                            continue;
                        }
                        if(i >= start && i <= end) {
                            table_body += `<td class="timetable-highlight"></td>`;
                            continue;
                        }
                        table_body += `<td></td>`;
                    }
                    table_body += `</tr>`;
                    count++;
                }

                return `
                <div>
                    <table width="100" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>0</th>
                                <th>1</th>
                                <th>2</th>
                                <th>3</th>
                                <th>4</th>
                                <th>5</th>
                                <th>6</th>
                                <th>7</th>
                                <th>8</th>
                                <th>9</th>
                                <th>10</th>
                                <th>11</th>
                                <th>12</th>
                                <th>13</th>
                                <th>14</th>
                                <th>15</th>
                                <th>16</th>
                                <th>17</th>
                                <th>18</th>
                                <th>19</th>
                                <th>20</th>
                                <th>21</th>
                                <th>22</th>
                                <th>23</th>
                            </tr>
                        </thead>
                        <tbody >
                            ${table_body}
                        </tbody>
                    </table> 
                </div>
                `;
            }

            function deleteUserShift(shift, element) {
                Swal.fire({
                    title: "Do you really want to remove shift?",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: "Remove"
                    }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('staf-time-manage.employee-schedule.destroy') }}",
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                user_id: shift.pivot.user_id,
                                shift_id: shift.pivot.shift_id
                            },
                            success: function(response) {
                                if(response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: response.message,
                                    });

                                    element.parentElement.parentElement.remove();
                                    return;
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: response.message,
                                });
                            },
                            error: function(error) {
                                if(error.status === 422) {
                                    const errors = error.responseJSON.errors;
                                    let message = '';
                                    for (const key in errors) {
                                        if (errors.hasOwnProperty(key)) {
                                            message += errors[key][0] + '<br>';
                                        }
                                    }

                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        html: message,
                                    });

                                    return;
                                }
                            }
                        });
                    }
                });
            }
        </script>        
    </div>
</x-app-layout>
