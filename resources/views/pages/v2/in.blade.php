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
        var sec = {{ (int)config('uno.timeout_in', 30) }} * 1000;
        var model = '';
        var lpr = '';
        var datecapture = '';
        var memberstatus = '';
        var hasResponse = false;
        var qrcodeInstance = null; // Reuse QR instance untuk performa cepat
        var globalTimer = null;

        function setDisplayTimeout(callback, delayMs) {
            if (globalTimer) {
                clearTimeout(globalTimer);
                globalTimer = null;
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
            $('#wrapper_data').removeClass('hidden');
        }
        function hideWrapperData() {
            $('#wrapper_data').addClass('hidden');
        }
        window.Echo.channel('{{ strtolower(config('app.name')) }}_database_my-channel')
            .listen('.my-event', (e) => {
                clearDisplayTimeout();
                blink();
                var jsonString = e.message;
                showWrapperData();
                try {
                    var jsonObject = JSON.parse(jsonString);
                    var datas = jsonObject;
                    var local_ip = datas.local_ip;
                    var posname = datas.posname;
                    var image = datas.image || '';
                    var job = datas.job;
                    var action = datas.action;
                    var posip = datas.posip;
                    
                    $('#promosi_operator').addClass('hidden');
                    $('#imagein').removeClass('hidden');
                    if (lpr == '') {
                        lpr = datas.lpr
                        model = datas.model;


                    }
                    
                    datecapture = datas.datecapture || '';
                    if (datas.memberstatus != '') {
                        memberstatus = datas.memberstatus + ' - ' + (datas.memberperiod || '');

                    }else{
                        $('#memberstatus').text('Non Member');
                    }
                    if (datas.memberperiod) {
                        var memberperiod = datas.memberperiod || '';
                    }

                    var pesan = datas.pesan;
                    if (image) { setimage(image, 'imagein'); }
                    $('#posname').text(posname);
                    $('#posip').text(posip);
                    $('#lpr').text(lpr);
                    $('#datecapture').text(datecapture);
                    $('#info').text(pesan);

                    setDisplayTimeout(function() {
                        clear();
                        hideWrapperData();
                        $('#info').text('Silahkan scan tiket atau tap kartu anda');
                    }, {{ (int)config('uno.timeout_in', 30) * 1000 }});
                } catch (error) {
                    console.error("Error parsing JSON: ", error);
                }

            })
            .listen('.my-event-out', (e) => {
                clearDisplayTimeout();
                showWrapperData();
                blink();
                var jsonString = e.message;
                
                var jsonObject = JSON.parse(jsonString);
                hasResponse = true;

                var datas = jsonObject;
                var action = datas.action;
                $('#promosi_operator').addClass('hidden');
                $('#imagein').removeClass('hidden');

                var local_ip = datas.local_ip;
                var job = datas.job;
                var posname = datas.posname;
                var posip = datas.posip;
                var image = datas.image || '';
                var imagein = datas.imagein || '';

                if (action == 1) {
                    lpr = datas.lpr || '';
                    model = datas.model || '';
                    datecapture = datas.datecapture || '';
                    memberstatus = datas.memberstatus || '';
                } else if (lpr == '') {
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
                var inpos = datas.inpos || '';
                var intime = datas.intime || '';
                var outtime = datas.outtime || '';
                var duration = datas.duration || '';
                var pesan = datas.pesan || '';
                var done = false;

                if (action == 1) {
                    $('#page-out').addClass('hidden');
                    $('#standby').removeClass('hidden');
                    $('#qr-container').addClass('hidden');
                    $('#image').addClass('hidden');
                    $('#informasi-pembayaran-row').addClass('hidden');
                    $('#informasi-pembayaran').text('');
                    
                    if (image) {
                        setimage(image, 'imagein');
                    } else {
                        $('#imagein').attr('src', `{{ asset('public/Logo_Operator.jpg') }}`);
                    }
                    $('#image').attr('src', `{{ asset('public/out.jpg') }}`);

                    $('#nota').text('');
                    $('#total').text('');
                    $('#duration').text('');
                    $('#intime').text('');
                    $('#outtime').text('');
                    $('#vehicletype').text('');

                    setDisplayTimeout(function() {
                        hasResponse = hasResponse ? !hasResponse : hasResponse;
                        action = 0;
                    }, {{ (int)config('uno.timeout_out', 30) * 1000 }});
                }
                 else if (action == 3) {
                    $('#standby').addClass('hidden');
                    $('#page-out').removeClass('hidden');
                    $('#image').addClass('hidden');
                    $('#qr-container').removeClass('hidden');
                    var qr = datas.qris;
                    var qrEl = document.getElementById('qr');
                    showWrapperData();
                    $('#expired').text('Masa Berlaku : ' + (datas.expired || '').replace(/\\\//g, '/'));

                    // requestAnimationFrame HANYA untuk render QR
                    requestAnimationFrame(function() {
                        if (qrEl && qr) {
                            qrEl.innerHTML = '';

                            qrcodeInstance = new QRCode(qrEl, {
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
                    
                    if (image) { setimage(image, 'imagein'); }

                    // Timeout dipasang di luar requestAnimationFrame
                    setDisplayTimeout(function() {
                        clear_out();
                        $('#promosi_operator').removeClass('hidden');
                        $('#info').text('Silahkan scan tiket atau tap kartu anda');
                    }, {{ (int)config('uno.timeout_out', 30) * 1000 }});
                } else if (action == 4) {
                    if (imagein) { setimage(imagein, 'image'); }
                    var qrEl = document.getElementById('qr');
                    if (qrEl) { qrEl.innerHTML = ''; }
                    $('#qr-container').addClass('hidden');
                    $('#page-out').removeClass('hidden');
                    $('#standby').addClass('hidden');
                    showWrapperData();
                    var balance = datas.balance;
                    if (image) { setimage(image, 'imagein'); }
                    $('#image').removeClass('hidden');
                    $('#informasi-pembayaran-row').removeClass('hidden');
                    if (balance) {
                        $('#informasi-pembayaran').text('Saldo : ' + formatRupiah(balance));
                    } else {
                        $('#informasi-pembayaran-row').addClass('hidden');
                        $('#informasi-pembayaran').addClass('hidden');
                    }
                    setDisplayTimeout(function() {
                        lpr = '';
                        model = '';
                        datecapture = '';
                        memberstatus = '';
                        clear_out();
                    }, {{ (int)config('uno.timeout_out_in', 30) * 1000 }});
                }

                $('#info').text(pesan);
                $('#posname').text(posname);
                $('#posip').text(posip);
                if(memberperiod == '') {
                    $('#memberstatus').text('Non Member');
                } else {
                    $('#memberstatus').text('Masa Aktif Member : ' + memberperiod);
                }
                $('#plate').text(plateno);
                $('#lpr').text(lpr);
                $('#datecapture').text(datecapture);
                if (action != 1) {
                    $('#nota').text(nota);
                    $('#total').text(formatRupiah(total));
                    $('#vehicletype').text(vehicletype);
                    $('#intime').text(intime);
                    $('#outtime').text(outtime);
                    $('#duration').text(duration);
                }
                $('#video').addClass('hidden');
                $('#imagein').removeClass('hidden');
                $('#labelin').removeClass('hidden');

            });

        function clear() {
            $('#memberstatus').text('\t');
            $('#lpr').text('\t');
            $('#datecapture').text('\t');
            hideWrapperData();
            $('#promosi_operator').removeClass('hidden');
            $('#imagein').addClass('hidden');
            $('#imagein').removeAttr('src');
            $('#imagein').attr('src', `{{ asset('public/Logo_Operator.jpg') }}`);
            $('#image').attr('src', `{{ asset('public/out.jpg') }}`);
            $('#info').text('Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.');

            lpr = '';
            model = '';
            datecapture = '';
            memberstatus = '';
        }

        function clear_out() {
            $('#memberstatus').text('\u00A0');
            $('#lpr').text('\u00A0');
            $('#plate').text('\u00A0');
            $('#datecapture').text('\u00A0');
            $('#nota').text('');
            $('#total').text('');
            hideWrapperData();
            $('#vehicletype').text('');
            $('#intime').text('');
            $('#outtime').text('');
            $('#duration').text('');
            $('#informasi-pembayaran').text(' ');

            $('#standby').removeClass('hidden');
            $('#page-out').addClass('hidden');
            $('#video').removeClass('hidden');
            $('#labelin').addClass('hidden');
            $('#imagein').addClass('hidden');
            $('#promosi_operator').removeClass('hidden');
            $('#image').attr('src', `{{ asset('public/out.jpg') }}`);
            $('#imagein').attr('src', `{{ asset('public/Logo_Operator.jpg') }}`);
            $('#info').text('Silahkan scan tiket atau tap kartu anda');
            lpr = '';
            model = '';
            datecapture = '';
            memberstatus = '';
            // $('#info').text('Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.');
        }

        function formatRupiah(amount) {
            const formatter = new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            return formattedAmount = formatter.format(amount);
        }

        function blink() {
            $('#wrapper-info').addClass('animate-blink');
            setTimeout(function() {
                $('#wrapper-info').removeClass('animate-blink');
            }, 2000);
        }
        // Set the video source
                // setVideo('\\\\192.168.9.223\\Share\\promosi.mp4');
        function syncLprWrapperHeight() {
            var wrapper = document.getElementById('wrapper');
            var lpr = document.getElementById('lpr_wrapper');
            if (wrapper && lpr) {
                lpr.style.height = wrapper.offsetHeight + 'px';
            }
        }
        $(window).on('resize load', function() {
            setTimeout(syncLprWrapperHeight, 100);
        });
        // Panggil setelah document ready
        $(document).ready(function() {
            syncLprWrapperHeight();
        });
    </script>
@endpush
