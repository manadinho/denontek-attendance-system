<div class="row row-cols-1 row-cols-sm-4 row-cols-lg-6 g-3">
    @forelse($attendances as $row)
      @php
        // Present if there's a check-in; tweak if you prefer (e.g., check-in OR check-out)
        $present = !empty($row['checkin_at']);
        $bgClass = $present ? 'bg-success' : 'bg-danger';
        $bgClass = $row['late_comer'] ? 'bg-warning' : $bgClass;
      @endphp

        <div class="col">
            <div class="card {{ $bgClass }} text-white shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <h5 class="card-title mb-2 fw-bold">{{ $row['name'] ?? '—' }}</h5>
                    </div>

                    <dl class="mb-0 small">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <dt class="opacity-75 mb-0 me-2">Reg #</dt>
                            <dd class="mb-0">{{ $row['registeration_id'] ?? '—' }}</dd>
                        </div>

                        <div class="d-flex align-items-center justify-content-between">
                            <dt class="opacity-75 mb-0 me-2">Phone</dt>
                            <dd class="mb-0">
                            @if(!empty($row['guardian_contact']))
                                <a class="link-light text-decoration-underline" href="tel:{{ $row['guardian_contact'] }}">
                                {{ $row['guardian_contact'] }}
                                </a>
                            @else
                                <span class="opacity-75">—</span>
                            @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    @empty
        <div class="text-muted">No attendance attendances found.</div>
    @endforelse
</div>