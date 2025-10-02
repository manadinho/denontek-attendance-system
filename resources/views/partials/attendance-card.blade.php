<div class="w-25" style="width:316.25px; height:110px; margin:0; margin-bottom:20px;">
  <div class="card device-card h-100">
    <div class="d-flex card-body h-100">
      <div class="d-flex align-items-center flex-grow-1 min-w-0">
        <div class="bg-dark text-white rounded-circle d-flex align-items-center justify-content-center me-2 flex-shrink-0" style="width:42px; height:42px;">
          <svg width="22" height="22" viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><g><path d="M31,26c-0.6,0-1-0.4-1-1V12c0-0.6,0.4-1,1-1s1,0.4,1,1v13C32,25.6,31.6,26,31,26z"/></g><g><path d="M16,21c-0.2,0-0.3,0-0.5-0.1l-15-8C0.2,12.7,0,12.4,0,12s0.2-0.7,0.5-0.9l15-8c0.3-0.2,0.6-0.2,0.9,0l15,8c0.3,0.2,0.5,0.5,0.5,0.9s-0.2,0.7-0.5,0.9l-15,8C16.3,21,16.2,21,16,21z"/></g><path d="M17.4,22.6C17,22.9,16.5,23,16,23s-1-0.1-1.4-0.4L6,18.1V22c0,3.1,4.9,6,10,6s10-2.9,10-6v-3.9L17.4,22.6z"/></svg>
        </div>
        <div class="min-w-0">
          <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size:1.05rem; line-height:1.1; max-width:138px;">
              {{$standard->name}}
          </h6>
          <small class="text-muted d-block text-truncate" style="font-size:0.8rem; line-height:1.1; max-width:138px;">
              {{$standard->students_count}} Student(s)
          </small>
        </div>
      </div>
      <div class="d-flex align-items-center gap-2 flex-shrink-0 ms-2" style="margin-top: -20px;">
        <div class="d-flex align-items-center gap-1">
          <span class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:24px; height:24px;" title="Present">
            <!-- <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9 16.2 4.8 12 3.4 13.4 9 19 21 7 19.6 5.6z"/></svg> -->
            <i class="bi bi-check fs-4"></i>
          </span>
          <span class="fw-semibold text-success" style="font-size:1rem;">
            {{ $standard->present_students }}
          </span>
        </div>
        <div class="d-flex align-items-center gap-1">
          <span class="bg-warning text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:24px; height:24px;" title="Late Comers">
            <!-- <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.3 5.7 12 12 5.7 5.7 4.3 7.1 10.6 13.4 4.3 19.7 5.7 21.1 12 14.8 18.3 21.1 19.7 19.7 13.4 13.4 19.7 7.1z"/></svg> -->
            <i class="bi bi-exclamation-lg fs-5"></i>
          </span>
          <span class="fw-semibold text-warning" style="font-size:1rem;">
            {{ $standard->late_students }}
          </span>
        </div>
        <div class="d-flex align-items-center gap-1">
          <span class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center" style="width:24px; height:24px;" title="Absent">
            <!-- <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18.3 5.7 12 12 5.7 5.7 4.3 7.1 10.6 13.4 4.3 19.7 5.7 21.1 12 14.8 18.3 21.1 19.7 19.7 13.4 13.4 19.7 7.1z"/></svg> -->
            <i class="bi bi-x fs-4"></i>
          </span>
          <span class="fw-semibold text-danger" style="font-size:1rem;">
            {{ $standard->absent_students }}
          </span>
        </div>
      </div>
    </div>

    <div class="text-end">
      <x-dark-button
        class="text-whtie btn-sm rounded-pill w-50 me-1"
        style="margin-top: -40px;width: 38% !important;padding-top: 3px !important;padding-bottom: 3px !important;padding: 0px;border-radius: 7px !important;margin-right: 19px !important;"
        x-data="{ standardId: {{ $standard->id }} }"
        @click.prevent="
          $dispatch('std-modal:open', { standardId });
          $dispatch('open-modal', 'student-attendence-detail-modal');
        "
      >
        <i class="bi bi-eye"></i> View Detail
      </x-dark-button>
    </div>
  </div>
</div>
