<div class="col-xl-3 col-lg-6 p-1">
    <div class="card card-stats mb-xl-0">
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <span class="h2 font-weight-bold mb-0">{{$standard->name}}</span>
                </div>
                <div class="col-auto">
                    <div class="p-2 icon icon-shape bg-danger text-white rounded-circle shadow">
                    <i class="fs-4 fas fa-chalkboard-teacher"></i>
                    </div>
                </div>
            </div>
            <p class="mt-3 mb-0 text-muted text-sm">
                <div class="row">
                    <div class="col-md-4 fs-6 fw-bold"><span class="text-dark"><i class="fas fa-users"></i> Total: {{$standard->students_count}}</span></div>
                    <div class="col-md-4 fs-6 fw-bold"><span class="text-success"><i class="fas fa-check"></i> Present: {{ $standard->present_students_count }}</span></div>
                    <div class="col-md-4 fs-6 fw-bold"><span class="text-danger"><i class="fas fa-times"></i> Absent: {{ $standard->students_count - $standard->present_students_count }}</span></div>
                </div>
            </p>
        </div>
    </div>
</div>