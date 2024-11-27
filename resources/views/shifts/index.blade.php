<style>
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
            @include('shifts.partials.shift-modal')
        </div>
        <div class="table-responsive mt-3">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="20%">Name</th>
                        <th width="15%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($shifts as $shift)
                    @php
                        if (is_string($shift->timetables)) {
                            $shift->timetables = json_decode($shift->timetables, true);
                        }

                        $isTimetableSet = false;
                        foreach($shift->timetables as $day => $timetable) {
                            if ($timetable) {
                                $isTimetableSet = true;
                            }
                        }
                    @endphp
                    <tr class="{{$isTimetableSet ? 'time-table-available': 'time-table-not-available'}}">
                        <td >
                                {{ $shift->name }}
                        </td>
                        <td>
                            <button class="btn btn-dark" onclick="viewTimetable('{{ $shift }}')">VIEW TIMETABLE</button>
                            <button class="btn btn-dark" onclick="addTimetable('{{ $shift }}')">ADD TIMETABLE</button>
                            <a href="javascript:void(0)" title="Edit" onclick="editShift({{$shift}})"><i class="fas fa-edit"></i></a>
                            <a href="javascript:void(0)" title="Delete" onclick="deleteShift('{{route('staf-time-manage.shifts.destroy', $shift->id)}}')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    @empty
                        <tr>
                            <td colspan="5">No Timetable Found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $shifts->links() }}
        </div>
        <div class="den-modal add-timetable-modal" style="visibility: hidden;">
            <div class="den-modal-content modal-large add-timetable-modal-content mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full sm:max-w-2xl sm:mx-auto">
                <!-- will add content here dynamically -->
            </div>
        </div>
        <div class="den-modal view-timetable-modal mb-6 bg-white rounded-lg overflow-hidden shadow-xl transform transition-all" style="visibility: hidden;">
            <div class="den-modal-content modal-large view-timetable-modal-content">
                <!-- will add content here dynamically -->
            </div>
        </div>
        <script>
            const TIMETABLES = @json($timetables);
            const SHIFT = null;

            function resetStaffModalForm() {
                // reset form
                $('#shift-form')[0].reset();
            }

            function editShift(timetable) {
                console.log(timetable);
                $('#id').val(timetable.id)
                $('#name').val(timetable.name);
                
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'shift-create-edit-modal' }));
            }

            function deleteShift(url) {
                Swal.fire({
                    title: "Do you really want to delete Shift?",
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: "Delete"
                    }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            }
            function toggleModal(modalSelector = '.den-modal') {
                const modal = document.querySelector(modalSelector);
                modal.style.visibility = modal.style.visibility == 'visible' ? 'hidden' : 'visible';
            }

            function addTimetable(shift)  {
                toggleModal('.add-timetable-modal');
                shift = JSON.parse(shift);
                const addTimeTableContent = generateAddTimetableContent(shift);
                document.querySelector('.add-timetable-modal-content').innerHTML = addTimeTableContent;
                timetableChange();

                const button = document.querySelector('#den-save-shift-timetable-btn');
                button.onclick = () => saveTimeTable(shift);
            }

            function closeTimetableModal() {
                toggleModal('.add-timetable-modal');
            }

            function generateAddTimetableContent(shift) {
                let table_body = ``;
                for(const day in shift.timetables){
                    table_body += `<tr>`;
                    table_body += `<td> ${day}</td>`;
                    table_body += `<td> ${drawTimetablesSelectTag(shift.timetables[day])}</td>`;
                    table_body += `</tr>`;
                }

                return `
                <div>
                    <div class="p-6">
                        <h2 class="text-lg font-medium text-gray-900 text-start">
                            Add Timetable
                        </h2>
                    </div>
                    <div style="padding:10px">
                        <table width="100" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th width="50%">Name</th>
                                    <th width="50%">Timetable</th>
                                </tr>
                            </thead>
                            <tbody >
                                ${table_body}
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-6 flex justify-end" style="padding:10px">
                        <button type="button" onclick="closeTimetableModal()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150" x-on:click="$dispatch('close')">
                            Close
                        </button>

                                        <button type="button" class="btn btn-dark ms-3" id="den-save-shift-timetable-btn">
                            Save
                        </button>
                    </div>
                </div>
                `;
            }

            function drawTimetablesSelectTag(selected_timetable_id){
                let select_content = `<select name="timetable[]" onchange="timetableChange()" class="den-select">`;
                
                select_content += `<option value="">Select a Timetable</option>`;

                for(const timetable of TIMETABLES) {
                    if(timetable.id == selected_timetable_id) {
                        select_content += `<option value="${timetable.id}" selected>${timetable.name}</option>`;
                        continue;
                    }

                    select_content += `<option value="${timetable.id}">${timetable.name}</option>`;
                }

                select_content+= `</select>`;

                return select_content;

            }

            function timetableChange() {
                const timetables_by_days = document.querySelectorAll('select[name="timetable[]"]');
                for(const timetable_by_day of timetables_by_days) {
                    if(timetable_by_day.value === '') {
                        timetable_by_day.style.border = '1px solid red';
                    }

                    if(timetable_by_day.value !== '') {
                        timetable_by_day.style.border = '1px solid green';
                    }

                    if(timetable_by_day.value === 'off') {
                        timetable_by_day.style.border = '1px solid orange';
                    }
                }
            }

            function saveTimeTable(shift) {
                const timetables_by_days = document.querySelectorAll('select[name="timetable[]"]');
                const days = ["MONDAY", "TUESDAY", "WEDNESDAY", "THURSDAY", "FRIDAY", "SATURDAY", "SUNDAY"];
                const timetables = {};
                let iterator = 0;
                for(const timetable_by_day of timetables_by_days) {
                    timetables[days[iterator++]] = timetable_by_day.value || null;
                }

                const url = '{{ route("staf-time-manage.shifts.add-timetables") }}'

                $.ajax({
                    url,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: shift.id,
                        timetables
                    },
                    success: function(response) {
                        if(!response.success) {     
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message,
                            });

                            return;
                        }

                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: response.message,
                        });
                        setTimeout(() => {
                            location.reload();
                        }, 500);

                    },
                    error: function(error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: error.message,
                        });
                    }
                })
            }

            function viewTimetable(shift) {
                toggleModal('.view-timetable-modal');
                shift = JSON.parse(shift);
                const viewTimeTableContent = generateViewTimetableContent(shift);
                document.querySelector('.view-timetable-modal-content').innerHTML = viewTimeTableContent;
            }

            function generateViewTimetableContent(shift) {
                let table_body = ``;
                for(const day in shift.timetables){ 
                    const timetable_id = shift.timetables[day];
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
                }

                return `
                <div>
                    <h2 class="den-modal-title">View Timetable</h2>
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
                
                <div class="mt-6 flex justify-end" style="padding:10px">
                    <button type="button" onclick="closeViewtableModal()" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150" x-on:click="$dispatch('close')">
                        Close
                    </button>
                </div>
                `;
            }

            function closeViewtableModal() {
                toggleModal('.view-timetable-modal');
            }
        </script>        
    </div>
</x-app-layout>
