<style>
    canvas { margin: auto; }
    h2 {display: none;}
</style>

<x-app-layout>
  <x-slot name="header">
    <h1 class="font-semibold text-xl text-gray-800 leading-tight">
      WhatsApp Linking
    </h1>
  </x-slot>

  <main class="py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <div id="waCard" class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 flex items-center justify-between border-b">
            <div class="flex items-center gap-3">
            
            <span class="inline-block h-3 w-3 rounded-full"></span>
            <h3 class="text-lg font-medium text-gray-900">WhatsApp Status</h3>
            </div>
            <div class="text-sm text-gray-500">
            {{-- optional timestamp if you pass one --}}
            </div>
        </div>

        <div class="px-6 py-5" id="whatsapp-content-section">
          <div>Loading...</div>
        </div>
      </div>
    </div>
  </main>
</x-app-layout>

<script src="https://cdn.jsdelivr.net/npm/qrcode/build/qrcode.min.js"></script>
<script>
  window.statusCompleted = true;
  refreshHtml()
  // setinterval of 5 seconds and make  a get request to route('connect-whatsapp.get-status') and update html in whatsapp-content-section
  setInterval(async function() {
    refreshHtml()
  }, 10000);

  async function refreshHtml()
  {
    if(!window.statusCompleted) return;

    window.statusCompleted = false;

    response = await fetch("{{ route('connect-whatsapp.get-status') }}");

    window.statusCompleted = true;

    data = await response.json();
    document.getElementById('whatsapp-content-section').innerHTML = data.html;
    if(data.session.status == 'disconnected') {
      console.log('0000000', stripOuterQuotes(data.session.data.qr) == 'waiting')
      if(stripOuterQuotes(data.session.data.qr) == 'waiting') {
        document.getElementById('whatsapp-content-section').innerHTML = 'Waiting for QR code...';
        refreshHtml();
        return;
      }

      QRCode.toCanvas(document.getElementById('qrcanvas'), stripOuterQuotes(data.session.data.qr), err => {
        if (err) console.error('QR error:', err);
        console.log('✅ QR rendered!');
      });
    }
  }

  async function destroyWaSession()
  {
    await fetch("{{ route('connect-whatsapp.destroy-session') }}");
    refreshHtml();
  }

  function stripOuterQuotes(s) {
    s = String(s).trim();
    if (s.length >= 2) {
      const q = s[0];
      if ((q === '"' || q === "'") && s[s.length - 1] === q) return s.slice(1, -1);
    }
    return s;
  }
</script>
