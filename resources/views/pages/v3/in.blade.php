@extends('v3')

@section('contents')
<div class="screen w-screen h-screen p-[0.6em] flex flex-col overflow-hidden" style="background:#021C3F;">

    {{-- Warning Banner for Internet / QRIS Issue --}}
    <div id="warning-container" class="{{ !empty($setting->warning_message) ? '' : 'hidden' }} shrink-0 p-2 flex justify-between rounded-[0.2em] mb-[0.6em]"
         style="background-color: {{ !empty($setting->warning_background) ? $setting->warning_background : 'red' }}; color: {{ !empty($setting->warning_font_color) ? $setting->warning_font_color : 'yellow' }}; font-weight: bold; font-size: {{ !empty($setting->warning_font_size) ? $setting->warning_font_size : 20 }}px;">
        <div class="text-center flex-grow">
            <span class="text-2xl">{{ $setting->warning_message ?? 'INTERNET GANGGUAN, TIDAK BISA BAYAR PAKAI QRIS' }}</span>
        </div>
    </div>

    <div id="warning-qris-container" class="hidden shrink-0 p-2 flex justify-between items-center rounded-[0.2em] mb-[0.6em]"
         style="background-color: {{ !empty($setting->warning_background) ? $setting->warning_background : 'red' }}; color: {{ !empty($setting->warning_font_color) ? $setting->warning_font_color : 'yellow' }}; font-weight: bold; font-size: {{ !empty($setting->warning_font_size) ? $setting->warning_font_size : 20 }}px;">
        <div class="text-center flex-grow">
            <span class="text-2xl font-bold" id="warning-qris-text">QRIS sedang dalam kendala</span>
        </div>
    </div>

    <div class="w-full flex-1 min-h-0 flex gap-[0.6em]">

        <!-- LEFT COLUMN -->
        <div class="flex-[1] min-w-0 flex flex-col gap-[0.6em]">

            <!-- Top Row: Logo / Lokasi / Jam + Tanggal -->
            <div class="flex gap-[0.6em] shrink-0">
                <div class="w-[8em] h-[3.6em] rounded-[0.2em] justify-center overflow-hidden shrink-0">
                    <img src="{{ file_exists(public_path('asset/logo.jpg')) ? asset('asset/logo.jpg') : (file_exists(public_path('Logo.png')) ? asset('Logo.png') : asset('Logo_Operator.jpg')) }}" alt="Logo" class="h-full object-fill" onerror="this.src='{{ asset('Logo_Operator.jpg') }}'">
                </div>
                <div class="flex-1 rounded-[0.2em] flex flex-col justify-center px-2">
                    <span class="font-bold text-white text-[1.5em] leading-tight" id="posname">{{ $setting->name ?? ($setting->text_promotion ?? 'LOKASI 1') }}</span>
                    <span class="text-white text-[0.6em] leading-tight" id="posip">{{ $setting->address ?? ($ip ?? 'ALAMAT LOKASI') }}</span>
                </div>
                <div class="w-[6.5em] text-white rounded-[0.2em] flex flex-col items-end justify-center leading-tight shrink-0 px-2">
                    <span id="jam" class="font-bold text-[1.5em]">10:10</span>
                    <span id="tanggal" class="text-[0.55em]">26 Desember 2026</span>
                </div>
            </div>

            <!-- Big Image Placeholder / Center Box -->
            <div class="flex-1 min-h-0 rounded-[0.5em] overflow-hidden bg-gray-300 flex flex-col">

                <!-- Media Bagian Atas: Standby Carousel OR Camera IN Picture -->
                <div class="flex-1 min-h-0 relative w-full h-full overflow-hidden bg-black flex items-center justify-center">

                    <!-- Carousel Promosi Operator saat Standby -->
                    <div id="promosi_operator" class="relative z-0 w-full h-full overflow-hidden"
                         @if(count($datas_operator) > 1) data-carousel="slide" data-carousel-interval="{{ ($setting->duration ?? 15) * 1000 }}" @endif>
                        @if(count($datas_operator) > 0)
                            @foreach ($datas_operator as $key => $item)
                                <div class="{{ $key != 0 ? 'hidden' : '' }} duration-2000 ease-in-out absolute inset-0 transition-transform transform z-20 translate-x-0"
                                     data-carousel-item="{{ $key == 0 ? 'active' : '' }}">
                                    @if ($item['type'] == 'image')
                                        <img loading="lazy" class="w-full h-full object-fill" src="{{ $item['path'] }}" alt="Promosi">
                                    @else
                                        <video src="{{ $item['path'] }}" loop autoplay muted playsinline class="w-full h-full object-fill block"></video>
                                    @endif
                                </div>
                            @endforeach
                        @else
                            <img src="{{ file_exists(public_path('cam/in.jpeg')) ? asset('cam/in.jpeg') : asset('Logo_Operator.jpg') }}" class="w-full h-full object-fill" onerror="this.src='{{ asset('Logo_Operator.jpg') }}'">
                        @endif
                    </div>

                    <!-- Gambar Kamera IN saat ada trigger kendaraan -->
                    <img id="imagein" src="{{ asset('Logo_Operator.jpg') }}" alt="Kamera IN" class="w-full h-full object-fill absolute inset-0 z-30 hidden" onerror="this.src='{{ asset('public/Logo_Operator.jpg') }}'">
                </div>

                <!-- Banner Orange: Pesan + Nopol + Member Status (hanya muncul saat ada trigger/event) -->
                <div class="shrink-0 bg-[#F79743] p-3 text-white font-bold text-[1.2em] text-center hidden" id="wrapper_data">
                    <span id="info">SELAMAT DATANG, TEKAN TOMBOL TIKET ATAU TAP KARTU</span>
                    <div class="text-[1.8em] font-extrabold tracking-wider leading-tight" id="lpr">&nbsp;</div>
                    <div class="text-[0.7em] font-semibold opacity-95" id="memberstatus">&nbsp;</div>
                </div>

                <!-- Metode Bayar -->
                <div class="shrink-0 bg-white">
                    <div class="text-center text-gray-600 py-3 font-bold text-[0.8em] tracking-wide">
                        METODE BAYAR
                    </div>
                    <div class="grid grid-cols-5 gap-[0.6em] px-[0.5em] pb-[0.5em]">
                        <div class="flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('method/Flazz.png') }}" alt="Flazz" class="w-full h-[1.5em] object-contain">
                        </div>
                        <div class="flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('method/emoney.png') }}" alt="e-money" class="w-full h-[1.5em] object-contain">
                        </div>
                        <div class="flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('method/brizzi.png') }}" alt="BRIZZI" class="w-full h-[1.5em] object-contain">
                        </div>
                        <div class="flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('method/tapcash.png') }}" alt="TapCash" class="w-full h-[1.5em] object-contain">
                        </div>
                        <div class="flex items-center justify-center overflow-hidden">
                            <img src="{{ asset('method/qris.png') }}" alt="QRIS" class="w-full h-[1.5em] object-contain">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Info Bar Footer Grid 4 -->
            <div class="shrink-0 text-white font-bold rounded-[0.2em] grid grid-cols-4 divide-x divide-gray-300 overflow-hidden">
                <div class="py-[0.5em] flex items-center justify-center text-[0.65em]">INFO LAYANAN</div>
                <div class="py-[0.5em] flex items-center justify-center text-[0.65em]">+62 888-1357-770</div>
                <div class="py-[0.5em] flex items-center justify-center text-[0.65em]">www.unosystem.id</div>
                <div class="py-[0.5em] flex items-center justify-center text-[0.65em]">info@unoparking.com</div>
            </div>

        </div>

        <!-- RIGHT COLUMN -->
        <div class="flex-[1] min-w-0 flex flex-col h-full">

            <!-- State 1: Standby / IN Gate -> Full-height Iklan Carousel -->
            <div id="standby" class="w-full h-full rounded-[0.2em] overflow-hidden bg-gray-300 relative z-0 flex flex-col"
                 @if(count($datas) > 1) data-carousel="slide" data-carousel-interval="{{ ($setting->duration ?? 15) * 1000 }}" @endif>
                @if(count($datas) > 0)
                    @foreach ($datas as $key => $item)
                        <div class="{{ $key != 0 ? 'hidden' : '' }} duration-2000 ease-in-out absolute inset-0 transition-transform transform z-20 translate-x-0"
                             data-carousel-item="{{ $key == 0 ? 'active' : '' }}">
                            @if ($item['type'] == 'image')
                                <img loading="lazy" class="w-full h-full object-fill" src="{{ $item['path'] }}" alt="Iklan">
                            @else
                                <video src="{{ $item['path'] }}" loop autoplay muted playsinline class="w-full h-full object-fill block" id="video"></video>
                            @endif
                        </div>
                    @endforeach
                @else
                    <img src="{{ file_exists(public_path('asset/iklan1.jpeg')) ? asset('asset/iklan1.jpeg') : asset('Logo_Operator.jpg') }}" class="w-full h-full object-fill" onerror="this.src='{{ asset('Logo_Operator.jpg') }}'">
                @endif
            </div>

            <!-- State 2 & 3: Out Gate Struk & Pembayaran (Action 3 / Action 4) -->
            <div id="page-out" class="hidden w-full h-full rounded-[0.6em] bg-[#F4F7FB] border-[0.15em] border-[#021C3F] flex flex-col gap-[0.7em] overflow-hidden p-4 shadow-xl">

                <div class="font-extrabold text-[#021C3F] text-[1.1em] tracking-wide shrink-0" id="page-out-title">
                    SCAN UNTUK BAYAR
                </div>

                <!-- ROOT 1: QRIS or Camera OUT -->
                <!-- 1A: QRIS Display (Action 3) -->
                <div id="qr-container" class="shrink-0 flex flex-col gap-[0.7em]">
                    <div class="flex gap-[0.9em]">
                        <!-- QR Code Box -->
                        <div class="bg-white rounded-[0.4em] border border-[#021C3F]/20 p-[0.5em] shrink-0 flex items-center justify-center shadow-sm"
                             style="width: 13.5em; height: 13.5em;" id="struk-qr-box">
                            <div id="qr" class="w-full h-full flex items-center justify-center"></div>
                        </div>

                        <!-- Total & Plat -->
                        <div class="flex-1 flex flex-col gap-[0.5em] min-w-0" id="struk-total-plat-col">
                            <div class="flex-1 rounded-[0.4em] overflow-hidden border border-orange-300 flex flex-col shadow-sm">
                                <div class="bg-orange-400 text-white text-center font-bold text-[0.65em] tracking-wide py-[0.25em] shrink-0">
                                    TOTAL
                                </div>
                                <div class="flex-1 bg-white flex items-center justify-center font-extrabold text-[#021C3F] text-[1.4em]" id="total">
                                    Rp0
                                </div>
                            </div>
                            <div class="flex-1 rounded-[0.4em] overflow-hidden border border-orange-300 flex flex-col shadow-sm">
                                <div class="bg-orange-400 text-white text-center font-bold text-[0.65em] tracking-wide py-[0.25em] shrink-0">
                                    PLAT NO
                                </div>
                                <div class="flex-1 bg-white flex items-center justify-center font-extrabold text-[#021C3F] text-[1.4em]" id="plate">
                                    -
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Berlaku Sampai -->
                    <div class="leading-tight">
                        <div class="font-bold text-[#021C3F] text-[0.8em]">BERLAKU SAMPAI</div>
                        <div class="font-bold text-red-600 text-[1.1em]" id="expired">-</div>
                    </div>
                </div>

                <!-- 1B: Camera OUT Image (Action 4 / Tap Card / Cash) -->
                <div id="image-out-container" class="shrink-0 hidden flex flex-col gap-[0.5em]">
                    <div class="w-full h-[24.5em] rounded-[0.4em] overflow-hidden border border-[#021C3F]/20 bg-black shadow-sm">
                        <img id="image" src="{{ asset('public/out.jpg') }}" alt="Camera Out" class="w-full h-full object-fill" onerror="this.src='{{ asset('out.jpg') }}'">
                    </div>
                    <div id="informasi-pembayaran-row" class="hidden text-center py-1.5 bg-green-100 rounded border border-green-300 shadow-sm">
                        <span id="informasi-pembayaran" class="font-bold text-green-700 text-[1.2em]"></span>
                    </div>
                </div>

                <!-- Garis Pemisah -->
                <div class="border-t-2 border-[#021C3F]/15 shrink-0 my-1"></div>

                <!-- ROOT 2: Detail Struk -->
                <div class="flex-1 min-h-0 flex flex-col justify-between gap-[0.35em] text-[0.95em]">
                    <div class="flex items-baseline justify-between gap-[0.5em] border-b border-dotted border-[#021C3F]/30 pb-[0.2em]">
                        <span class="font-bold text-[#021C3F]">STATUS MEMBER</span>
                        <span class="font-bold text-[#021C3F]" id="struk-memberstatus">NON MEMBER</span>
                    </div>
                    <div class="flex items-baseline justify-between gap-[0.5em] border-b border-dotted border-[#021C3F]/30 pb-[0.2em]">
                        <span class="font-bold text-[#021C3F]">NO NOTA</span>
                        <span class="font-bold text-[#021C3F]" id="nota">-</span>
                    </div>
                    <div class="flex items-baseline justify-between gap-[0.5em] border-b border-dotted border-[#021C3F]/30 pb-[0.2em]">
                        <span class="font-bold text-[#021C3F]">KENDARAAN</span>
                        <span class="font-bold text-[#021C3F]" id="vehicletype">-</span>
                    </div>
                    <div class="flex items-baseline justify-between gap-[0.5em] border-b border-dotted border-[#021C3F]/30 pb-[0.2em]">
                        <span class="font-bold text-[#021C3F]">TANGGAL MASUK</span>
                        <span class="font-bold text-[#021C3F]" id="intime">-</span>
                    </div>
                    <div class="flex items-baseline justify-between gap-[0.5em] border-b border-dotted border-[#021C3F]/30 pb-[0.2em]">
                        <span class="font-bold text-[#021C3F]">TANGGAL KELUAR</span>
                        <span class="font-bold text-[#021C3F]" id="outtime">-</span>
                    </div>
                    <div class="flex items-baseline justify-between gap-[0.5em] border-b border-dotted border-[#021C3F]/30 pb-[0.2em]">
                        <span class="font-bold text-[#021C3F]">DURASI</span>
                        <span class="font-bold text-[#021C3F]" id="duration">-</span>
                    </div>
                </div>

            </div>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    const bulan = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];

    function updateClock() {
        const now = new Date();
        const hh = String(now.getHours()).padStart(2, '0');
        const mm = String(now.getMinutes()).padStart(2, '0');
        const ss = String(now.getSeconds()).padStart(2, '0');
        const jamEl = document.getElementById('jam');
        const tglEl = document.getElementById('tanggal');
        if (jamEl) jamEl.textContent = `${hh}:${mm}:${ss}`;
        if (tglEl) tglEl.textContent = `${now.getDate()} ${bulan[now.getMonth()]} ${now.getFullYear()}`;
    }
    updateClock();
    setInterval(updateClock, 1000);

    function syncTotalPlatHeight() {
        const qrBox = document.getElementById('struk-qr-box');
        const totalPlatCol = document.getElementById('struk-total-plat-col');
        if (qrBox && totalPlatCol) {
            totalPlatCol.style.height = qrBox.offsetHeight + 'px';
        }
    }
    syncTotalPlatHeight();
    window.addEventListener('resize', syncTotalPlatHeight);

    const LOGO_OPERATOR_URL = `{{ asset('public/Logo_Operator.jpg') }}`;
    const OUT_IMG_URL = `{{ asset('public/out.jpg') }}`;
    const TIMEOUT_IN = {{ (int)config('uno.timeout_in', 30) * 1000 }};
    const TIMEOUT_OUT = {{ (int)config('uno.timeout_out', 30) * 1000 }};
    const TIMEOUT_OUT_IN = {{ (int)config('uno.timeout_out_in', 30) * 1000 }};

    var model = '';
    var lpr = '';
    var datecapture = '';
    var memberstatus = '';
    var hasResponse = false;
    var qrcodeInstance = null;
    var globalTimer = null;
    var isPaymentActive = false;
    var activeTicket = null;

    var DOM = {};
    function initDOM() {
        DOM = {
            wrapperData: $('#wrapper_data'),
            promosiOperator: $('#promosi_operator'),
            imagein: $('#imagein'),
            image: $('#image'),
            imageOutContainer: $('#image-out-container'),
            memberstatus: $('#memberstatus'),
            strukMemberstatus: $('#struk-memberstatus'),
            posname: $('#posname'),
            posip: $('#posip'),
            lpr: $('#lpr'),
            info: $('#info'),
            plate: $('#plate'),
            nota: $('#nota'),
            total: $('#total'),
            vehicletype: $('#vehicletype'),
            intime: $('#intime'),
            outtime: $('#outtime'),
            duration: $('#duration'),
            pageOut: $('#page-out'),
            pageOutTitle: $('#page-out-title'),
            standby: $('#standby'),
            qrContainer: $('#qr-container'),
            qrEl: document.getElementById('qr'),
            infoPembayaranRow: $('#informasi-pembayaran-row'),
            infoPembayaran: $('#informasi-pembayaran'),
            expired: $('#expired'),
            video: $('#video'),
            warningQrisContainer: $('#warning-qris-container'),
            warningQrisText: $('#warning-qris-text')
        };
    }

    const rupiahFormatter = new Intl.NumberFormat("id-ID", {
        style: "currency",
        currency: "IDR",
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });

    function formatRupiah(amount) {
        return rupiahFormatter.format(amount || 0);
    }

    function setDisplayTimeout(callback, delayMs) {
        if (globalTimer) {
            clearTimeout(globalTimer);
        }
        globalTimer = setTimeout(callback, delayMs);
    }

    function clearDisplayTimeout() {
        if (globalTimer) {
            clearTimeout(globalTimer);
            globalTimer = null;
        }
    }

    function showWrapperData() {
        DOM.wrapperData.removeClass('hidden');
    }

    function hideWrapperData() {
        DOM.wrapperData.addClass('hidden');
    }

    function blink() {
        DOM.wrapperData.addClass('animate-blink');
        setTimeout(function() {
            DOM.wrapperData.removeClass('animate-blink');
        }, 2000);
    }

    $(document).ready(function() {
        initDOM();
        syncTotalPlatHeight();
    });

    // Socket.IO Watchdog & Reconnection Handler
    if (window.Echo && window.Echo.connector && window.Echo.connector.socket) {
        var socket = window.Echo.connector.socket;
        var reconnectTimeout = null;

        socket.on('disconnect', function(reason) {
            console.warn('[Socket] Disconnected:', reason);
            clearTimeout(reconnectTimeout);
            reconnectTimeout = setTimeout(function() {
                console.warn('[Socket] Disconnected for 15s. Reloading page...');
                window.location.reload();
            }, 15000);
        });

        socket.on('connect', function() {
            console.log('[Socket] Connected, ID:', socket.id);
            clearTimeout(reconnectTimeout);
        });

        socket.on('reconnect', function(attemptNumber) {
            console.log('[Socket] Reconnected after attempts:', attemptNumber);
            clearTimeout(reconnectTimeout);
        });

        setInterval(function() {
            if (socket && !socket.connected) {
                console.warn('[Socket Watchdog] Socket not connected, attempting connect...');
                socket.connect();
            }
        }, 10000);
    }

    // Auto reload harian pada jam 03:00 pagi
    setInterval(function() {
        var now = new Date();
        if (now.getHours() === 3 && now.getMinutes() === 0 && now.getSeconds() < 10) {
            window.location.reload();
        }
    }, 10000);

    // Live Socket Listeners
    window.Echo.channel('{{ strtolower(config('app.name')) }}_database_my-channel')
        .listen('.my-event', (e) => {
            if (isPaymentActive) {
                console.warn('[Display] InEvent diabaikan karena QRIS sedang aktif di OUT.');
                return;
            }

            clearDisplayTimeout();
            blink();

            try {
                var datas = typeof e.message === 'string' ? JSON.parse(e.message) : e.message;
                var posname = datas.posname;
                var image = datas.image || '';
                var posip = datas.posip;

                showWrapperData();
                DOM.promosiOperator.addClass('hidden');
                DOM.imagein.removeClass('hidden');

                if (lpr == '') {
                    lpr = datas.lpr || '';
                    model = datas.model || '';
                }

                datecapture = datas.datecapture || '';
                if (datas.memberstatus) {
                    memberstatus = datas.memberstatus + ' - ' + (datas.memberperiod || '');
                    DOM.memberstatus.text(memberstatus);
                } else {
                    DOM.memberstatus.text('NON MEMBER');
                }

                var pesan = datas.pesan || 'SELAMAT DATANG, TEKAN TOMBOL TIKET ATAU TAP KARTU';
                if (image) { setimage(image, 'imagein'); }

                if (posname) DOM.posname.text(posname);
                if (posip) DOM.posip.text(posip);
                DOM.lpr.text(lpr || '---');
                DOM.info.text(pesan);

                setDisplayTimeout(function() {
                    clear();
                }, TIMEOUT_IN);
            } catch (error) {
                console.error("Error parsing JSON: ", error);
            }
        })
        .listen('.my-event-out', (e) => {
            var datas;
            try {
                datas = typeof e.message === 'string' ? JSON.parse(e.message) : e.message;
            } catch (err) {
                console.error("Error parsing out JSON:", err);
                return;
            }

            hasResponse = true;
            var action = datas.action;
            var nota = datas.nota || '';
            var pesan = datas.pesan || '';
            var total = isNaN(Number(datas.total)) ? 0 : Number(datas.total);

            if (action != 3 && DOM.warningQrisContainer) {
                DOM.warningQrisContainer.addClass('hidden');
            }

            if (isPaymentActive && action == 1) {
                var isExplicitCancel = (pesan && (pesan.toLowerCase().includes('batal') || pesan.toLowerCase().includes('cancel'))) ||
                                       (datas.is_cancel === true) ||
                                       (datas.cancel === true);
                if (isExplicitCancel) {
                    console.log('[Display] Transaksi QRIS dibatalkan secara eksplisit.');
                    isPaymentActive = false;
                    activeTicket = null;
                    clear_out();
                } else {
                    console.warn('[Display] Action 1 diabaikan karena QRIS sedang aktif untuk tiket:', activeTicket);
                    return;
                }
            }

            showWrapperData();
            DOM.promosiOperator.addClass('hidden');
            DOM.imagein.removeClass('hidden');

            var posname = datas.posname;
            var posip = datas.posip;
            var image = datas.image || '';
            var imagein = datas.imagein || '';

            if (action == 1 || lpr == '') {
                lpr = datas.lpr || '';
                model = datas.model || '';
                datecapture = datas.datecapture || '';
                memberstatus = datas.memberstatus || '';
            }

            var memberperiod = datas.memberperiod || '';
            var plateno = datas.plateno || '';
            var vehicletype = datas.vehicletype || '';
            var intime = datas.intime || '';
            var outtime = datas.outtime || '';
            var duration = datas.duration || '';

            if (action == 1) {
                clearDisplayTimeout();
                blink();

                DOM.pageOut.addClass('hidden');
                DOM.standby.removeClass('hidden');
                DOM.qrContainer.addClass('hidden');
                DOM.imageOutContainer.addClass('hidden');
                DOM.infoPembayaranRow.addClass('hidden');
                DOM.infoPembayaran.text('');

                if (image) {
                    setimage(image, 'imagein');
                } else {
                    DOM.imagein.attr('src', LOGO_OPERATOR_URL);
                }
                DOM.image.attr('src', OUT_IMG_URL);

                DOM.nota.text('-');
                DOM.total.text('Rp0');
                DOM.duration.text('-');
                DOM.intime.text('-');
                DOM.outtime.text('-');
                DOM.vehicletype.text('-');

                var timeoutMs = (datas.timeout_out && Number(datas.timeout_out) > 0)
                    ? Number(datas.timeout_out)
                    : TIMEOUT_OUT;

                setDisplayTimeout(function() {
                    hasResponse = hasResponse ? !hasResponse : hasResponse;
                    action = 0;
                    clear_out();
                }, timeoutMs);
            }
            else if (action == 3) {
                var timeoutMs = (datas.timeout_out && Number(datas.timeout_out) > 0)
                    ? Number(datas.timeout_out)
                    : TIMEOUT_OUT;

                if (activeTicket && nota && activeTicket !== nota) {
                    if (DOM.qrEl) { DOM.qrEl.innerHTML = ''; }
                    qrcodeInstance = null;
                }

                if (isPaymentActive && activeTicket && activeTicket === nota) {
                    console.log('[Display] Action 3 untuk tiket yang sama (' + nota + '), timer tetap berjalan.');
                } else {
                    isPaymentActive = true;
                    activeTicket = nota;

                    clearDisplayTimeout();
                    blink();

                    setDisplayTimeout(function() {
                        isPaymentActive = false;
                        activeTicket = null;
                        clear_out();
                    }, timeoutMs);
                }

                DOM.standby.addClass('hidden');
                DOM.pageOut.removeClass('hidden');
                DOM.pageOutTitle.text('SCAN UNTUK BAYAR');

                var qr = datas.qris;
                var isConnected = (datas.connected !== undefined && datas.connected !== null) ? Number(datas.connected) : null;
                var isKendala = Boolean(isConnected === 0 || datas.warning_qris || (pesan && pesan.toLowerCase().includes('kendala')));
                var showQris = Boolean(qr && !isKendala);

                if (showQris) {
                    DOM.imageOutContainer.addClass('hidden');
                    DOM.qrContainer.removeClass('hidden');
                    if (DOM.warningQrisContainer) { DOM.warningQrisContainer.addClass('hidden'); }
                } else {
                    DOM.qrContainer.addClass('hidden');
                    DOM.imageOutContainer.removeClass('hidden');
                    if (DOM.qrEl) { DOM.qrEl.innerHTML = ''; }
                    qrcodeInstance = null;

                    if (isKendala && DOM.warningQrisContainer) {
                        DOM.warningQrisContainer.removeClass('hidden');
                        if (DOM.warningQrisText) {
                            DOM.warningQrisText.text(datas.warning_qris || 'QRIS sedang dalam kendala');
                        }
                    }
                }

                DOM.expired.text(showQris ? (datas.expired || '').replace(/\\\//g, '/') : '-');

                if (DOM.qrEl && showQris) {
                    requestAnimationFrame(function() {
                        try {
                            DOM.qrEl.innerHTML = '';
                            var qrBox = document.getElementById('struk-qr-box');
                            var qrSize = qrBox ? (qrBox.offsetWidth - 20) : 180;
                            qrcodeInstance = new QRCode(DOM.qrEl, {
                                text: qr,
                                width: qrSize,
                                height: qrSize,
                                colorDark: '#000000',
                                colorLight: '#ffffff',
                                correctLevel: QRCode.CorrectLevel.M
                            });
                            syncTotalPlatHeight();
                        } catch (qrErr) {
                            console.error('Error generating QR:', qrErr);
                        }
                    });
                }

                if (image) { setimage(image, 'imagein'); }
            }
            else if (action == 4) {
                isPaymentActive = false;
                activeTicket = null;

                clearDisplayTimeout();
                blink();

                if (imagein) { setimage(imagein, 'image'); }
                if (DOM.qrEl) { DOM.qrEl.innerHTML = ''; }
                qrcodeInstance = null;

                DOM.qrContainer.addClass('hidden');
                DOM.imageOutContainer.removeClass('hidden');
                DOM.pageOut.removeClass('hidden');
                DOM.standby.addClass('hidden');
                DOM.pageOutTitle.text('INFORMASI PEMBAYARAN');

                var balance = datas.balance;
                if (image) { setimage(image, 'imagein'); }

                if (balance) {
                    DOM.infoPembayaran.text('Saldo : ' + formatRupiah(balance));
                    DOM.infoPembayaranRow.removeClass('hidden');
                } else {
                    DOM.infoPembayaranRow.addClass('hidden');
                }

                var timeoutOutInMs = (datas.timeout_out_in && Number(datas.timeout_out_in) > 0)
                    ? Number(datas.timeout_out_in)
                    : TIMEOUT_OUT_IN;

                setDisplayTimeout(function() {
                    lpr = '';
                    model = '';
                    datecapture = '';
                    memberstatus = '';
                    clear_out();
                }, timeoutOutInMs);
            }

            if (posname) DOM.posname.text(posname);
            if (posip) DOM.posip.text(posip);

            var memText = memberperiod ? ('Member s/d ' + memberperiod) : 'NON MEMBER';
            DOM.memberstatus.text(memText);
            DOM.strukMemberstatus.text(memText);

            DOM.plate.text(plateno || lpr || '-');
            DOM.lpr.text(plateno || lpr || '---');

            if (action == 3 && (typeof isKendala !== 'undefined' && isKendala) && pesan && pesan.toLowerCase().includes('kendala')) {
                DOM.info.text('SILAHKAN MELAKUKAN PEMBAYARAN');
            } else {
                DOM.info.text(pesan || 'SILAHKAN PROSES KENDARAAN');
            }

            if (action != 1) {
                DOM.nota.text(nota || '-');
                DOM.total.text(formatRupiah(total));
                DOM.vehicletype.text(vehicletype || '-');
                DOM.intime.text(intime || '-');
                DOM.outtime.text(outtime || '-');
                DOM.duration.text(duration || '-');
            }

            syncTotalPlatHeight();
        });

    function clear() {
        isPaymentActive = false;
        activeTicket = null;
        clearDisplayTimeout();

        if (DOM.warningQrisContainer) { DOM.warningQrisContainer.addClass('hidden'); }
        if (DOM.qrEl) { DOM.qrEl.innerHTML = ''; }
        qrcodeInstance = null;

        hideWrapperData();
        DOM.memberstatus.text('\u00A0');
        DOM.lpr.text('\u00A0');
        DOM.promosiOperator.removeClass('hidden');
        DOM.imagein.addClass('hidden');
        DOM.imagein.attr('src', LOGO_OPERATOR_URL);
        DOM.image.attr('src', OUT_IMG_URL);
        DOM.info.text('SELAMAT DATANG, TEKAN TOMBOL TIKET ATAU TAP KARTU');

        lpr = '';
        model = '';
        datecapture = '';
        memberstatus = '';
    }

    function clear_out() {
        isPaymentActive = false;
        activeTicket = null;
        clearDisplayTimeout();

        if (DOM.warningQrisContainer) { DOM.warningQrisContainer.addClass('hidden'); }
        if (DOM.qrEl) { DOM.qrEl.innerHTML = ''; }
        qrcodeInstance = null;

        hideWrapperData();
        DOM.memberstatus.text('\u00A0');
        DOM.strukMemberstatus.text('NON MEMBER');
        DOM.lpr.text('\u00A0');
        DOM.plate.text('-');
        DOM.nota.text('-');
        DOM.total.text('Rp0');
        DOM.vehicletype.text('-');
        DOM.intime.text('-');
        DOM.outtime.text('-');
        DOM.duration.text('-');
        DOM.infoPembayaran.text('');
        DOM.infoPembayaranRow.addClass('hidden');

        DOM.standby.removeClass('hidden');
        DOM.pageOut.addClass('hidden');
        DOM.imagein.addClass('hidden');
        DOM.promosiOperator.removeClass('hidden');
        DOM.image.attr('src', OUT_IMG_URL);
        DOM.imagein.attr('src', LOGO_OPERATOR_URL);
        DOM.info.text('SELAMAT DATANG, TEKAN TOMBOL TIKET ATAU TAP KARTU');

        lpr = '';
        model = '';
        datecapture = '';
        memberstatus = '';
    }
</script>
@endpush
