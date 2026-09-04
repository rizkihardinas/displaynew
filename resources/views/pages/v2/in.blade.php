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

        window.Echo.channel('{{ strtolower(config('app.name')) }}_database_my-channel')
            .listen('.my-event', (e) => {
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
                        DOM.info.text('Silahkan scan tiket atau tap kartu anda');
                    }, TIMEOUT_IN);
                } catch (error) {
                    console.error("Error parsing JSON: ", error);
                }
            })
            .listen('.my-event-out', (e) => {
                clearDisplayTimeout();
                showWrapperData();
                blink();

                var datas;
                try {
                    datas = typeof e.message === 'string' ? JSON.parse(e.message) : e.message;
                } catch (err) {
                    console.error("Error parsing out JSON:", err);
                    return;
                }

                hasResponse = true;
                var action = datas.action;
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
                var nota = datas.nota || '';
                var plateno = datas.plateno || '';
                var total = isNaN(Number(datas.total)) ? 0 : Number(datas.total);
                var vehicletype = datas.vehicletype || '';
                var intime = datas.intime || '';
                var outtime = datas.outtime || '';
                var duration = datas.duration || '';
                var pesan = datas.pesan || '';

                if (action == 1) {
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

                    setDisplayTimeout(function() {
                        hasResponse = hasResponse ? !hasResponse : hasResponse;
                        action = 0;
                    }, TIMEOUT_OUT);
                }
                else if (action == 3) {
                    DOM.standby.addClass('hidden');
                    DOM.pageOut.removeClass('hidden');
                    DOM.image.addClass('hidden');
                    DOM.qrContainer.removeClass('hidden');
                    showWrapperData();

                    var qr = datas.qris;
                    DOM.expired.text('Masa Berlaku : ' + (datas.expired || '').replace(/\\\//g, '/'));

                    if (DOM.qrEl && qr) {
                        requestAnimationFrame(function() {
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
                                    correctLevel: QRCode.CorrectLevel.M,
                                    useSVG: true
                                });
                            }
                        });
                    }

                    if (image) { setimage(image, 'imagein'); }

                    setDisplayTimeout(function() {
                        clear_out();
                        DOM.promosiOperator.removeClass('hidden');
                        DOM.info.text('Silahkan scan tiket atau tap kartu anda');
                    }, TIMEOUT_OUT);
                }
                else if (action == 4) {
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

                    setDisplayTimeout(function() {
                        lpr = '';
                        model = '';
                        datecapture = '';
                        memberstatus = '';
                        clear_out();
                    }, TIMEOUT_OUT_IN);
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
