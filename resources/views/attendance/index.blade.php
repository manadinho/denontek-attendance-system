{{-- resources/views/attendance/files.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold fs-4 text-dark">
            Attendance Sync
        </h2>
    </x-slot>

    <div class="py-3">
        <div class="container">

            {{-- Legend --}}
            <div class="d-flex align-items-center gap-4 small mb-3">
                <span class="d-inline-flex align-items-center gap-2">
                    <span class="rounded-circle bg-success" style="width:10px; height:10px;"></span> Synced
                </span>
                <span class="d-inline-flex align-items-center gap-2">
                    <span class="rounded-circle bg-danger" style="width:10px; height:10px;"></span> Not Synced
                </span>
            </div>

            {{-- Grid: 4 per row on lg+ --}}
            {{-- Tabs header --}}
            <ul class="nav nav-tabs mb-3" id="deviceTabs" role="tablist">
                @foreach ($devices as $idx => $device)
                    <li class="nav-item" role="presentation">
                        <a
                            class="nav-link {{ $selectedDevice->id == $device->id ? 'active' : '' }}"
                            aria-controls="pane-{{ $device->id }}"
                            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
                            href="{{ route('attendance-sync.index', $device->id) }}"
                        >
                        <i class="bi bi-router-fill"></i>
                            {{ $device->name ?? ('Device #'.$device->id) }}
                        </a>
                    </li>
                @endforeach
            </ul>

            {{-- Tabs content --}}
            <div class="tab-content" id="deviceTabsContent">
                    <div
                        class="tab-pane fade show active"
                        id="pane-{{ $selectedDevice->id }}"
                        role="tabpanel"
                        aria-labelledby="tab-{{ $selectedDevice->id }}"
                        tabindex="0"
                    >
                        @php $files = $selectedDevice->attendance_files ?? []; @endphp

                        @if (empty($files) || (is_countable($files) && count($files) === 0))
                            <div class="alert alert-secondary mb-0">
                                No attendance files for this device.
                            </div>
                        @else
                            @if($files->where('synced', 0)->count())
                                <div class="d-flex justify-content-end">
                                    <button class="btn btn-dark mb-2" onclick="syncAttendanceFile('{{ $files->where("synced", 0)->first()->name }}')"><i class="bi bi-arrow-repeat me-1"></i>Sync Missing Attendance</button>
                                </div>
                            @endif
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3">
                                @foreach ($files as $attendanceFile)
                                    @php
                                        $border = !empty($attendanceFile['synced']) ? 'border-success' : 'border-danger';
                                    @endphp

                                    <div class="col">
                                        <div class="card h-100 {{ $border }} position-relative" @unless(!empty($attendanceFile['synced']))
                                            @endunless>
                                            <div class="card-body">
                                                <h6 class="card-title mt-2 mb-1 text-truncate fw-bold">
                                                    @if(!empty($attendanceFile['synced']))
                                                        <i class="bi bi-check-circle-fill text-success"></i>
                                                    @else
                                                        <i class="bi bi-x-circle-fill text-danger"></i>
                                                    @endif
                                                    {{ \Carbon\Carbon::parse($attendanceFile['date'])->format('M d Y') }} ({{$attendanceFile['type']}})
                                                </h6>
                                                <div class="small text-muted">
                                                    {{ \Carbon\Carbon::parse($attendanceFile['date'])->format('l') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
            </div>

        </div>
    </div>
</x-app-layout>
