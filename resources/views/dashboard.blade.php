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

    @include('partials.notice-board')

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

    <!-- student attendance detail modal -->
    <x-modal name="student-attendence-detail-modal" id="student-attendence-detail-modal" focusable maxWidth="8xl">
        <div class="p-6"
            x-data="stdModal()"
            @std-modal:open.window="onOpen($event.detail)">

            <div class="flex items-center justify-between">
                <h2 class="text-lg font-medium text-gray-900">
                    <span x-text="title" class="fw-bold"></span>
                </h2>

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    @click="$dispatch('close')"
                    aria-label="Close"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>

            <div class="mt-6 text-center" x-show="isLoading" x-cloak>
                <div class="spinner-border text-primary" role="status" aria-label="Loading"></div>
                <div class="small text-muted mt-2">Loading…</div>
            </div>

            <div class="mt-6 text-start" x-show="!isLoading" x-cloak>
                <div x-html="studentAttendanceHtml"></div>
            </div>
        </div>
    </x-modal>

</x-app-layout>

<script>
function stdModal() {
  return {
    standardId: null,
    title: 'Student Attendance',
    isLoading: false,
    studentAttendanceHtml: '',

    onOpen(payload = {}) {
      if (!payload?.standardId) return;
      this.standardId = payload.standardId;
      this.studentAttendanceHtml = '';
      this.load();
    },

    async load() {
      try {
        this.isLoading = true;
        const res = await fetch(`/standards/get-today-attendance/${this.standardId}`, {
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        const data = await res.json();
        this.studentAttendanceHtml = data?.success ? (data.attendanceHtml || '') :
          '<div class="text-danger">No data found.</div>';
        this.title = data?.standardName ? `Student Attendance - ${data.standardName}` : 'Student Attendance';
      } catch (e) {
        console.error(e);
        this.studentAttendanceHtml = '<div class="text-danger">Failed to load attendance.</div>';
      } finally {
        this.isLoading = false;
      }
    }
  }
}
</script>
