<style>
    .circle-online {
        width: 22px;
        height: 22px;
        background-color: #62bd19;
        border-radius: 50%;
        position: absolute;
        margin-left: -28px;
        margin-top: 6px;
        box-shadow: 0px 1px 11px 0px #62bd19;
        -webkit-box-shadow: 0px 1px 11px 0px #62bd19;
        -moz-box-shadow: 0px 1px 11px 0px #62bd19;
    }
    .circle-offline {
        width: 22px;
        height: 22px;
        background-color: #F00;
        border-radius: 50%;
        position: absolute;
        margin-left: -28px;
        margin-top: 6px;
        box-shadow: 0px 1px 11px 0px #F00;
        -webkit-box-shadow: 0px 1px 11px 0px #F00;
        -moz-box-shadow: 0px 1px 11px 0px #F00;
    }
    .server-status-card{
        box-shadow: 0px 1px 11px 0px rgba(0,0,0,0.75);
        -webkit-box-shadow: 0px 1px 11px 0px rgba(0,0,0,0.75);
        -moz-box-shadow: 0px 1px 11px 0px rgba(0,0,0,0.75);
        min-height: 135px;
    }
</style>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>


    <h2 class="fs-3 fw-bold mt-3 mb-3">Device Statuses</h2>
    <div class="row">
        @forelse($devices as $device)
            @include("partials.device-status-card", ['device' => $device])
        @empty
        @endforelse
    </div>

    <hr class="mt-5 mb-5">
    
    <h2 class="fs-3 fw-bold mt-3 mb-3">Standards</h2>
    <div class="mt-3">
        <div class="row" id="standard-attendance-cards">
            
        </div>
    </div>
</x-app-layout>