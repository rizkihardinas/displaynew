@extends('v2')
@section('contents')
    @php
        $landscape = true;

    @endphp
    <div class="{{ $landscape ? 'grid grid-cols-2 gap-2 h-full overflow-hidden' : '' }}">

    @include('components.lpr')
    @include('components.in')
    @include('components.out')
        

    

    </div>
@endsection
@push('scripts')
    <script>
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

        // Cached DOM Elements
        var DOM = {};
        function initDOM() {
            DOM = {
                wrapperData: $('#wrapper_data'),
                promosiOperator: $('#promosi_operator'),
                imagein: $('#imagein'),
                image: $('#image'),
                memberstatus: $('#memberstatus'),
                posname: $('#posname'),
                posip: $('#posip'),
                lpr: $('#lpr'),
                datecapture: $('#datecapture'),
                info: $('#info'),
                plate: $('#plate'),
                nota: $('#nota'),
                total: $('#total'),
                vehicletype: $('#vehicletype'),
                intime: $('#intime'),
                outtime: $('#outtime'),
                duration: $('#duration'),
                pageOut: $('#page-out'),
                standby: $('#standby'),
                qrContainer: $('#qr-container'),
                qrEl: document.getElementById('qr'),
                infoPembayaranRow: $('#informasi-pembayaran-row'),
                infoPembayaran: $('#informasi-pembayaran'),
                expired: $('#expired'),
                video: $('#video'),
                labelin: $('#labelin'),
                wrapperInfo: $('#wrapper-info'),
                wrapper: document.getElementById('wrapper'),
                lprWrapper: document.getElementById('lpr_wrapper')
            };
        }

        // Global single instance Rupiah formatter
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
            DOM.wrapperInfo.addClass('animate-blink');
            setTimeout(function() {
                DOM.wrapperInfo.removeClass('animate-blink');
            }, 2000);
        }

        function syncLprWrapperHeight() {
            if (DOM.wrapper && DOM.lprWrapper) {
                DOM.lprWrapper.style.height = DOM.wrapper.offsetHeight + 'px';
            }
        }

        $(document).ready(function() {
            initDOM();
            syncLprWrapperHeight();
        });

        $(window).on('resize', function() {
            requestAnimationFrame(syncLprWrapperHeight);
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

        // Auto reload harian pada jam 03:00 pagi untuk membersihkan memory leak
        setInterval(function() {
            var now = new Date();
            if (now.getHours() === 3 && now.getMinutes() === 0 && now.getSeconds() < 10) {
                window.location.reload();
            }
        }, 10000);

        window.Echo.channel('{{ strtolower(config('app.name')) }}_database_my-channel')
            .listen('.my-event', (e) => {
                // Jika QRIS/pembayaran di OUT sedang aktif, abaikan event IN agar tidak memotong QRIS
                if (isPaymentActive) {
                    console.warn('[Display] InEvent diabaikan karena QRIS sedang aktif di OUT.');
                    return;
                }

                clearDisplayTimeout();
                blink();
                showWrapperData();

                try {
                    var datas = typeof e.message === 'string' ? JSON.parse(e.message) : e.message;
                    var posname = datas.posname;
                    var image = datas.image || '';
                    var posip = datas.posip;

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
                        DOM.memberstatus.text('Non Member');
                    }

                    var pesan = datas.pesan || '';
                    if (image) { setimage(image, 'imagein'); }

                    DOM.posname.text(posname);
                    DOM.posip.text(posip);
                    DOM.lpr.text(lpr);
                    DOM.datecapture.text(datecapture);
                    DOM.info.text(pesan);

                    setDisplayTimeout(function() {
                        clear();
                        hideWrapperData();
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

                // Proteksi: Jika QRIS sedang aktif, jangan biarkan action 1 (trigger loop sensor/idle check) memotong QRIS sebelum timeout
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
                    showWrapperData();
                    blink();

                    DOM.pageOut.addClass('hidden');
                    DOM.standby.removeClass('hidden');
                    DOM.qrContainer.addClass('hidden');
                    DOM.image.addClass('hidden');
                    DOM.infoPembayaranRow.addClass('hidden');
                    DOM.infoPembayaran.text('');

                    if (image) {
                        setimage(image, 'imagein');
                    } else {
                        DOM.imagein.attr('src', LOGO_OPERATOR_URL);
                    }
                    DOM.image.attr('src', OUT_IMG_URL);

                    DOM.nota.text('');
                    DOM.total.text('');
                    DOM.duration.text('');
                    DOM.intime.text('');
                    DOM.outtime.text('');
                    DOM.vehicletype.text('');

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

                    // Jika QRIS untuk tiket yang SAMA sedang berjalan, pertahankan timer yang sudah berjalan
                    if (isPaymentActive && activeTicket && activeTicket === nota) {
                        console.log('[Display] Action 3 untuk tiket yang sama (' + nota + '), timer tetap berjalan.');
                    } else {
                        isPaymentActive = true;
                        activeTicket = nota;

                        clearDisplayTimeout();
                        showWrapperData();
                        blink();

                        console.log('[Display] QRIS aktif untuk tiket: ' + (nota || '-') + ' | Durasi Timeout: ' + (timeoutMs / 1000) + ' detik');

                        setDisplayTimeout(function() {
                            console.log('[Display] Timeout QRIS tercapai (' + (timeoutMs / 1000) + ' detik). Mengembalikan ke standby...');
                            isPaymentActive = false;
                            activeTicket = null;
                            clear_out();
                            DOM.promosiOperator.removeClass('hidden');
                            DOM.info.text('Silahkan scan tiket atau tap kartu anda');
                        }, timeoutMs);
                    }

                    DOM.standby.addClass('hidden');
                    DOM.pageOut.removeClass('hidden');

                    var qr = datas.qris;
                    var isConnected = (datas.connected !== undefined && datas.connected !== null) ? Number(datas.connected) : null;
                    var showQris = Boolean(qr && (isConnected === null || isConnected === 1));

                    if (showQris) {
                        DOM.image.addClass('hidden');
                        DOM.qrContainer.removeClass('hidden');
                    } else {
                        DOM.qrContainer.addClass('hidden');
                        DOM.image.removeClass('hidden');
                        if (DOM.qrEl) { DOM.qrEl.innerHTML = ''; }
                        qrcodeInstance = null;
                    }
                    showWrapperData();

                    DOM.expired.text(showQris ? ('Masa Berlaku : ' + (datas.expired || '').replace(/\\\//g, '/')) : '');

                    if (DOM.qrEl && showQris) {
                        requestAnimationFrame(function() {
                            try {
                                if (qrcodeInstance) {
                                    qrcodeInstance.makeCode(qr);
                                } else {
                                    DOM.qrEl.innerHTML = '';
                                    qrcodeInstance = new QRCode(DOM.qrEl, {
                                        text: qr,
                                        width: 200,
                                        height: 200,
                                        colorDark: '#000000',
                                        colorLight: '#ffffff',
                                        correctLevel: QRCode.CorrectLevel.M
                                    });
                                }
                            } catch (qrErr) {
                                console.error('Error generating QR:', qrErr);
                                try {
                                    DOM.qrEl.innerHTML = '';
                                    qrcodeInstance = new QRCode(DOM.qrEl, {
                                        text: qr,
                                        width: 200,
                                        height: 200,
                                        colorDark: '#000000',
                                        colorLight: '#ffffff',
                                        correctLevel: QRCode.CorrectLevel.L
                                    });
                                } catch (e) {
                                    console.error('Fallback QR generation failed:', e);
                                }
                            }
                        });
                    }

                    if (image) { setimage(image, 'imagein'); }
                }
                else if (action == 4) {
                    isPaymentActive = false;
                    activeTicket = null;

                    clearDisplayTimeout();
                    showWrapperData();
                    blink();

                    if (imagein) { setimage(imagein, 'image'); }
                    if (DOM.qrEl) { DOM.qrEl.innerHTML = ''; }
                    qrcodeInstance = null;

                    DOM.qrContainer.addClass('hidden');
                    DOM.pageOut.removeClass('hidden');
                    DOM.standby.addClass('hidden');
                    showWrapperData();

                    var balance = datas.balance;
                    if (image) { setimage(image, 'imagein'); }
                    DOM.image.removeClass('hidden');
                    DOM.infoPembayaranRow.removeClass('hidden');

                    if (balance) {
                        DOM.infoPembayaran.text('Saldo : ' + formatRupiah(balance)).removeClass('hidden');
                    } else {
                        DOM.infoPembayaranRow.addClass('hidden');
                        DOM.infoPembayaran.addClass('hidden');
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

                DOM.info.text(pesan);
                DOM.posname.text(posname);
                DOM.posip.text(posip);

                if (memberperiod == '') {
                    DOM.memberstatus.text('Non Member');
                } else {
                    DOM.memberstatus.text('Masa Aktif Member : ' + memberperiod);
                }

                DOM.plate.text(plateno);
                DOM.lpr.text(lpr);
                DOM.datecapture.text(datecapture);

                if (action != 1) {
                    DOM.nota.text(nota);
                    DOM.total.text(formatRupiah(total));
                    DOM.vehicletype.text(vehicletype);
                    DOM.intime.text(intime);
                    DOM.outtime.text(outtime);
                    DOM.duration.text(duration);
                }

                DOM.video.addClass('hidden');
                DOM.imagein.removeClass('hidden');
                DOM.labelin.removeClass('hidden');
            });

        function clear() {
            isPaymentActive = false;
            activeTicket = null;
            clearDisplayTimeout();

            if (DOM.qrEl) { DOM.qrEl.innerHTML = ''; }
            qrcodeInstance = null;
            if (DOM.qrContainer) { DOM.qrContainer.addClass('hidden'); }

            DOM.memberstatus.text('\t');
            DOM.lpr.text('\t');
            DOM.datecapture.text('\t');
            hideWrapperData();
            DOM.promosiOperator.removeClass('hidden');
            DOM.imagein.addClass('hidden');
            DOM.imagein.attr('src', LOGO_OPERATOR_URL);
            DOM.image.attr('src', OUT_IMG_URL);
            DOM.info.text('Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.');

            lpr = '';
            model = '';
            datecapture = '';
            memberstatus = '';
        }

        function clear_out() {
            isPaymentActive = false;
            activeTicket = null;
            clearDisplayTimeout();

            if (DOM.qrEl) { DOM.qrEl.innerHTML = ''; }
            qrcodeInstance = null;
            if (DOM.qrContainer) { DOM.qrContainer.addClass('hidden'); }

            DOM.memberstatus.text('\u00A0');
            DOM.lpr.text('\u00A0');
            DOM.plate.text('\u00A0');
            DOM.datecapture.text('\u00A0');
            DOM.nota.text('');
            DOM.total.text('');
            hideWrapperData();
            DOM.vehicletype.text('');
            DOM.intime.text('');
            DOM.outtime.text('');
            DOM.duration.text('');
            DOM.infoPembayaran.text(' ');

            DOM.standby.removeClass('hidden');
            DOM.pageOut.addClass('hidden');
            DOM.video.removeClass('hidden');
            DOM.labelin.addClass('hidden');
            DOM.imagein.addClass('hidden');
            DOM.promosiOperator.removeClass('hidden');
            DOM.image.attr('src', OUT_IMG_URL);
            DOM.imagein.attr('src', LOGO_OPERATOR_URL);
            DOM.info.text('Silahkan scan tiket atau tap kartu anda');

            lpr = '';
            model = '';
            datecapture = '';
            memberstatus = '';
        }
    </script>
@endpush
