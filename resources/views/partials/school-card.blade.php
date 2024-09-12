<a href="{{route('select-school', $school->id)}}">
    <div class="col-xl-3 col-lg-6 p-1">
        <div class="card card-stats mb-xl-0">
            <div class="card-body">
                <div class="row">
                    <div class="col">
                        <span class="h2 font-weight-bold mb-0">{{$school->name}}</span>
                    </div>
                    <div class="col-auto">
                        <div class="p-2 icon icon-shape bg-danger text-white rounded-circle shadow">
                        <i class="fs-4 fas fa-school"></i>
                        </div>
                    </div>
                </div>
                <p class="mt-3 mb-0 text-muted fs-6 fw-bold">
                    {{$school->address}}
                </p>
            </div>
        </div>
    </div>
</a>