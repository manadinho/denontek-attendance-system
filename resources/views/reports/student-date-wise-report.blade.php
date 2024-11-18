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
            {{ __('Students Date Wise Report') }}
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
                    <label for="Standars">Select Standard</label>
                    <select name="standard" id="standard" class="form-control rounded" required>
                        <option value="">Select Standard</option>
                        @foreach($standards as $standard)
                            <option value="{{ $standard->id }}">{{ $standard->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="Standars">Select Students</label>
                    <select name="students[]" id="students" class="form-control rounded" multiple="multiple">
                        <option value="">Select Student</option>
                    </select>
                </div>
                <div class="form-group col-md-6 mt-2">
                    <x-primary-button id="submit-btn">
                        {{ __('Generate Report') }}
                    </x-primary-button>
                </div>
            </div>
            <div class="mt-3 p-4 sm:p-8 bg-warning txt-white sm:rounded-lg row">
                <p class="">
                    <strong>Note</strong><br>
                    <strong>P:</strong> for present.
                    <strong>A:</strong> for absent.
                    <strong>W:</strong> for week off.
                </p>
            </div>
            <div style="overflow-x: scroll;">
                <table class="table table-striped table-bordered mt-5">
                    <thead id="table-header">
                        
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
                $('#students').select2();
                // Handle selection change
                $('#students').on('select2:select', function(e) {
                    var selectedValue = e.params.data.id;
                    
                    if (selectedValue === 'select_all') {
                        // If "Select All" is selected, select all other options
                        $('#students > option').prop("selected", true);
                        $('#students').trigger("change");
                    }
                });

                // Handle deselecting "Select All"
                $('#students').on('select2:unselect', function(e) {
                    var unselectedValue = e.params.data.id;
                    
                    if (unselectedValue === 'select_all') {
                        // If "Select All" is unselected, deselect all options
                        $('#students > option').prop("selected", false);
                        $('#students').trigger("change");
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

                document.querySelector('#standard').addEventListener('change', function() {
                    // unselect all students
                    $('#students > option').prop("selected", false);
                    $('#students').trigger("change");
                    
                    var standard = this.value;
                    var url = "{{ route('students.get-students-by-standard') }}";
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            standard_id: standard
                        },
                        success: function(response) {
                            if(!response.success) {     
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: 'Attendance is already being synced!',
                                });l
                            }

                            var options = '<option value="select_all">Select All</option>';
                            response.students.forEach(function(student) {
                                options += '<option value="'+student.id+'">'+student.name+'</option>';
                            });

                            document.querySelector('#students').innerHTML = options;


                        },
                        error: function(error) {
                            console.log(error);
                        }
                    })
                });

                document.querySelector('#submit-btn').addEventListener('click', async function() {
                    let submitBtn = this;
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Generating Report...';

                    // reset the table, ATTENDANCE_REPORT, DAYS and DATES
                    document.querySelector('#table-header').innerHTML = '';
                    document.querySelector('#table-body').innerHTML = '';

                    window.ATTENDANCE_REPORT = [];
                    window.DAYS = [];
                    window.DATES = [];

                    var from = document.querySelector('#from').value;
                    var to = document.querySelector('#to').value;
                    var standard = document.querySelector('#standard').value;
                    var students = $('#students').val();

                    // validate data
                    if (!from || !to || !standard || !students) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Please fill all the fields!',
                        });
                        return;
                    }

                    // from date should be less than to date
                    if (new Date(from) > new Date(to)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'From date should be less than to date!',
                        });
                        return;
                    }

                    generateDatesAndDays(from, to);

                    // students should not be empty and should be an array
                    if (!Array.isArray(students) || students.length === 0) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Please select students!',
                        });
                        return;
                    }


                    students = students.filter(function(student) {
                        return student !== 'select_all';
                    });

                    const chunkSize = 5;
                    // loop over students and give two students to generate report method
                    for (let i = 0; i < students.length; i += chunkSize) {
                        // Create a chunk of students
                        let _students = students.slice(i, i + chunkSize);
                        
                        // Call the method and pass the _students
                        await generateReport(from, to, _students);
                    }

                    // Generate the report
                    drawReportHeader();
                    drawReportBody();
                });
            });

            function generateReport(from, to, students) {
                return new Promise((resolve, reject) => {
                    
                    var url = "{{ route('reports.students-date-wise-report.generate') }}";
                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            from,
                            to,
                            students
                        },
                        success: function(response) {
                            
                            if(response.success) {     
                                window.ATTENDANCE_REPORT = [...window.ATTENDANCE_REPORT, ...response.data];
                            }

                            resolve();
                        },
                        error: function(error) {
                            console.log(error);
                            resolve();
                        }
                    });
                });
            }

            function generateDatesAndDays(fromDate, toDate) {
                // Parse the fromDate and toDate
                let startDate = new Date(fromDate);
                let endDate = new Date(toDate);
                let daysArray = [];
                let datesArray = [];

                // Loop through each date from start to end
                while (startDate <= endDate) {
                    let formattedDate = startDate.toLocaleDateString('en-GB').split('/').reverse().join('-');
                    let day = startDate.toLocaleDateString('en-US', { weekday: 'long' }).toUpperCase();

                    daysArray.push(day);
                    datesArray.push(formattedDate);

                    // Move to the next day
                    startDate.setDate(startDate.getDate() + 1);
                }

                window.DAYS = [...daysArray];
                window.DATES = [...datesArray];
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
