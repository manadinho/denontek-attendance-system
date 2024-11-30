<style>
    table {
    border-collapse: collapse;
    width: 100%;
    overflow-x: auto; /* Enable horizontal scroll */
}

tbody td:first-child,
thead th:first-child {
    position: sticky;
    left: 0; /* Stick the first column to the left */
    background-color: #fff; /* Add background color to the first column */
    z-index: 1; /* Ensure the first column stays above the content */
}
</style>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Employee Check-in/Check-out Report') }}
        </h2>
    </x-slot>

    <div class="mt-3">
        <div class="text-end">
        </div>
        <div class="table-responsive mt-3">
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg row">
                <div class="form-group col-md-2">
                    <label for="from">From</label>
                    <input type="text" id="from" name="from" class="form-control rounded" autocomplete="off">
                </div>
                <div class="form-group col-md-2">
                    <label for="to">To</label>
                    <input type="text" id="to" name="to" class="form-control rounded" autocomplete="off">
                </div>
                <div class="form-group col-md-2">
                    <label for="employees">Select Employees</label>
                    <select name="employees[]" id="employees" class="form-control rounded" multiple="multiple">
                        <option value="select_all">Select All</option>
                        @forelse($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @empty
                            <option value="">No Employees found</option>
                        @endforelse
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <!-- Nothing to show -->
                </div>
                <div class="form-group col-md-6 mt-2">
                    <x-primary-button id="submit-btn">
                        {{ __('Generate Report') }}
                    </x-primary-button>
                </div>
            </div>
            <div style="overflow-x: scroll;">
                <table class="table table-striped table-bordered mt-5">
                    <thead id="table-header">
                        <th>date</th>
                        <th>day</th>
                        <th>id</th>
                        <th>name</th>
                        <th>shift</th>
                        <th>timetable</th>
                        <th>on time</th>
                        <th>off time</th>
                        <th>checkin</th>
                        <th>checkout</th>
                        <th>in status</th>
                        <th>out status</th>
                    </thead>
                    <tbody id="table-body">
                    </tbody>
                </table>
            </div>
        </div>
        <script>
            window.DAYS = [];
            window.DATES = [];
            window.ATTENDANCE_REPORT = [];
            window.WEEK_OFF_DAYS = @json($weekOffDays);

            document.addEventListener('DOMContentLoaded', function() {
                $('#employees').select2();
                // Handle selection change
                $('#employees').on('select2:select', function(e) {
                    var selectedValue = e.params.data.id;
                    
                    if (selectedValue === 'select_all') {
                        // If "Select All" is selected, select all other options
                        $('#employees > option').prop("selected", true);
                        $('#employees').trigger("change");
                    }
                });

                // Handle deselecting "Select All"
                $('#employees').on('select2:unselect', function(e) {
                    var unselectedValue = e.params.data.id;
                    
                    if (unselectedValue === 'select_all') {
                        // If "Select All" is unselected, deselect all options
                        $('#employees > option').prop("selected", false);
                        $('#employees').trigger("change");
                    }
                });

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

                document.querySelector('#submit-btn').addEventListener('click', async function() {
                    let submitBtn = this;
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Generating Report...';

                    var from = document.querySelector('#from').value;
                    var to = document.querySelector('#to').value;
                    var employees = $('#employees').val();

                    // validate data
                    if (!from || !to || !employees) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Please fill all the fields!',
                        });
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Generate Report';
                        return;
                    }

                    // from date should be less than to date
                    if (new Date(from) > new Date(to)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'From date should be less than to date!',
                        });
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Generate Report';
                        return;
                    }

                    // employees should not be empty and should be an array
                    if (!Array.isArray(employees) || employees.length === 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Please select employees!',
                        });
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Generate Report';
                        return;
                    }


                    employees = employees.filter(function(employee) {
                        return employee !== 'select_all';
                    });

                    document.querySelector('#table-body').innerHTML = '';

                    generateReport(from, to, employees);
                });
            });

            function generateReport(from, to, employees) { 
                var url = "{{ route('reports.employees-checkin-checkout-report.generate') }}";
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        from,
                        to,
                        employees
                    },
                    success: function(response) {
                        if(response.success) {
                            let html = '';
                            Object.values(response.data).forEach(data => {
                                let row = '';
                                if(data.day_of_week) {
                                    row = `<tr>
                                    <td>${data.date}</td>
                                    <td>${data.day}</td>
                                    <td colspan="10" style="background:#f3f398 !important; text-align: center; font-weight: bold;">WEEK OFF DAY</td>
                                </tr>`;
                                } else if(!data.checkin && !data.checkout) {
                                    row = `<tr>
                                    <td>${data.date}</td>
                                    <td>${data.day}</td>
                                    <td>${data.employeeId}</td>
                                    <td>${data.employee}</td>
                                    <td>${data.shift}</td>
                                    <td>${data.timetable}</td>
                                    <td>${data.on_time}</td>
                                    <td>${data.off_time}</td>
                                    <td colspan="4" style="background:#f38585 !important; text-align: center; font-weight: bold;">ABSENT</td>
                                    </tr>`;
                                } else {
                                    row = `<tr>
                                    <td>${data.date}</td>
                                    <td>${data.day}</td>
                                    <td>${data.employeeId}</td>
                                    <td>${data.employee}</td>
                                    <td>${data.shift}</td>
                                    <td>${data.timetable}</td>
                                    <td>${data.on_time}</td>
                                    <td>${data.off_time}</td>
                                    <td>${data.checkin ?? '-'}</td>
                                    <td>${data.checkout ?? '-'}</td>
                                    <td>${data.checkin_status}</td>
                                    <td>${data.checkout_status}</td>
                                </tr>`;
                                }
                                
                                html += row;
                            });
                            document.querySelector('#table-body').innerHTML = html;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message,
                            });
                        }

                        const submitBtn = document.querySelector('#submit-btn');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Generate Report';
                    },
                    error: function(error) {
                        const submitBtn = document.querySelector('#submit-btn');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Generate Report';
                        console.log(error);
                    }
                });
            }


            function drawReportHeader() {
                const headerRow = document.getElementById('table-header');

                // first column for student name
                const th = document.createElement('th');
                th.style.fontSize = '10px';
                th.style.textAlign = 'center';

                th.textContent = "Name";
                headerRow.appendChild(th);

                // Create table header cells for each date
                window.DATES.forEach((date, iterator) => {
                    const th = document.createElement('th');
                    th.style.fontSize = '10px';
                    th.style.textAlign = 'center';

                    const [year, month, day] = date.split('-'); // Split the date into parts
                    const formattedDate = `${month}/${day}`;    // Format as "MM/DD"

                    th.textContent = window.DAYS[iterator].charAt(0) + ' ' + month + '/' + day;
                    headerRow.appendChild(th);
                });
            }

            function drawReportBody() {
                const tableBody = document.getElementById('table-body');

                // Create a single tooltip element and add it to the body
                const tooltip = document.createElement('div');
                tooltip.style.position = 'absolute';
                tooltip.style.visibility = 'hidden';
                tooltip.style.backgroundColor = 'black';
                tooltip.style.color = 'white';
                tooltip.style.padding = '5px';
                tooltip.style.borderRadius = '3px';
                tooltip.style.fontSize = '10px';
                tooltip.style.whiteSpace = 'pre-line';
                tooltip.style.padding = '5px';
                tooltip.style.zIndex = '1000';
                document.body.appendChild(tooltip);

                window.ATTENDANCE_REPORT.forEach(entry => {
                    const row = document.createElement('tr');

                    // Create cell for student name
                    const nameCell = document.createElement('td');
                    nameCell.style.fontSize = '10px';
                    nameCell.style.textAlign = 'center';
                    nameCell.textContent = entry.student.name;
                    row.appendChild(nameCell);

                    // Loop through each date and check attendance
                    window.DATES.forEach((date, iterator) => {
                        const attendanceCell = document.createElement('td');
                        attendanceCell.style.fontSize = '10px';
                        attendanceCell.style.textAlign = 'center';

                        // Check if the date exists in the attendance object
                        if (entry.attendance.hasOwnProperty(date)) {
                            attendanceCell.textContent = 'P'; // Present
                            attendanceCell.style.backgroundColor = '#98f398';
                        } else {
                            if(window.WEEK_OFF_DAYS.includes(window.DAYS[iterator])) {
                                attendanceCell.textContent = 'W'; // Week Off
                                attendanceCell.style.backgroundColor = '#f3f398';
                            } else {
                                attendanceCell.textContent = 'A'; // Absent
                                attendanceCell.style.backgroundColor = '#f38585';
                            }
                        }

                        // Show tooltip on hover
                        attendanceCell.addEventListener('mouseover', (e) => {
                            let checkin =  entry.attendance.hasOwnProperty(date) ? entry.attendance[date].split('|')[0] : 'N/A';
                            let checkout =  entry.attendance.hasOwnProperty(date) ? entry.attendance[date].split('|')[1] : 'N/A';
                            tooltip.textContent = `${entry.student.name} \n Check-in: ${checkin} \n Check-out: ${checkout}`;
                            tooltip.style.left = `${e.pageX + 10}px`;
                            tooltip.style.top = `${e.pageY + 10}px`;
                            tooltip.style.visibility = 'visible';
                        });

                        // Hide tooltip when not hovering
                        attendanceCell.addEventListener('mouseout', () => {
                            tooltip.style.visibility = 'hidden';
                        });

                        row.appendChild(attendanceCell);
                    });

                    tableBody.appendChild(row);
                });

                // Enable the submit button
                let submitBtn = document.querySelector('#submit-btn');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Generate Report';
            }

        </script>        
    </div>
</x-app-layout>
