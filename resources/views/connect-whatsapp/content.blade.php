@php
    $isConnected = (isset($session) && $session['status'] === 'connected' && !empty($session['data']['user']));
    $user = $isConnected ? $session['data']['user'] : null;
@endphp

@if ($isConnected)
    @php
        // Extract number from "923076929940:85@s.whatsapp.net"
        $rawId   = $user['id'] ?? '';
        $number  = preg_replace('/[:@].*$/', '', (string)$rawId); // "923076929940"
        // Simple pretty format for PK numbers; tweak as needed
        $pretty  = preg_match('/^92\d{10}$/', $number)
                ? '+92 ' . substr($number, 2, 3) . '-' . substr($number, 5, 3) . '-' . substr($number, 8, 4)
                : ($number ? '+' . $number : '—');
    @endphp

    <div class="flex items-start gap-4">
        <div>
        <div class="text-sm uppercase tracking-wide text-green-700 font-semibold">✅ Connected</div>
        <div class="mt-1 text-gray-900">
            <span class="font-medium">{{ $pretty }}</span>
            <span class="text-gray-500">({{ $user['name'] ?? '—' }})</span>
        </div>
        <button
            onclick="destroyWaSession(this)"
            class="btn btn-danger mt-3">
            Connect Another Device
        </button>
        </div>
    </div>
@else
    <div class="flex items-center justify-center h-64">
        <div class="text-center">
        <div class="text-sm uppercase tracking-wide text-red-700 font-semibold mb-2">❌ Not connected</div>
        <div class="text-gray-700 mb-4">Scan the QR to link your device.</div>
        <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-gray-500 font-semibold">
            <canvas id="qrcanvas"></canvas>
        </div>
        </div>
    </div>
@endif