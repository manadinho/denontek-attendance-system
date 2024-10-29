<div class="col-md-4">
    <div class="card">
        <div class="card-body" style="text-align: center">
            <h2 id="device-chip-{{$device->chip_id}}" class="fs-3" style="text-align: center">
                <span class="hidden circle-online"></span> 
                <span class="circle-offline"></span> 
                <strong class="ms-2">{{ $device->name }}</strong>
            </h2>
            <h6 class="fs-6">(<strong>Type:</strong> {{ ucfirst($device->type) }})</h6>
        </div>
    </div>
</div>