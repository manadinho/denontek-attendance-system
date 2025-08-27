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
    body {
        background-color: #f8f9fa;
    }
    
    .device-card {
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .device-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    
    .device-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: white;
    }
    
    .icon-router { background: linear-gradient(135deg, #e7e9ec, #7c7d80); }
    
    .status-online {
        background-color: #d1e7dd;
        color: #0a3622;
        border: 1px solid #a3cfbb;
    }
    
    .status-offline {
        background-color: #f8d7da;
        color: #58151c;
        border: 1px solid #f1aeb5;
    }
    
    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    
    .dot-online {
        background-color: #198754;
        box-shadow: 0 0 8px rgba(25, 135, 84, 0.4);
    }
    
    .dot-offline {
        background-color: #dc3545;
    }
        
</style>
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>


    <h2 class="fs-3 fw-bold mt-3 mb-3">Device Statuses</h2>
    <div class="d-flex row">
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