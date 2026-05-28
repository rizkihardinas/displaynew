@extends('v2')
@section('contents')
    @php
        $landscape = true;

    @endphp
    <div class="{{ $landscape ? 'grid grid-cols-2 gap-2 h-full overflow-hidden' : '' }}">

    <div class="rounded-md h-full overflow-hidden">

    <div class="flex flex-col {{ config('uno.style.secondary') }} text-white h-full overflow-hidden">

        @include('components.lpr')

    </div>

</div>

    <div id="wrapper" class="h-full overflow-hidden">

        <div id="standby" class="w-full h-full">
            @include('components.in')
        </div>

        <div id="page-out" class="hidden w-full h-full">
            @include('components.out')
        </div>

    </div>

</div>
    </div>
@endsection
@push('scripts')
    <script>
        var sec = {{ config('uno.timeout_in') }} * 1000;
        var model = '';
        var lpr = '';
        var datecapture = '';
        var memberstatus = '';
        var hasResponse = false;
        var qrcodeInstance = null; // Reuse QR instance untuk performa cepat
        window.Echo.channel('{{ strtolower(config('app.name')) }}_database_my-channel')
            .listen('.my-event', (e) => {
                blink();
                var jsonString = e.message;
                $('#wrapper_data').removeClass('hidden');
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
                    if (datas.memberstatus) {
                        memberstatus = datas.memberstatus + ' - ' + (datas.memberperiod || '');

                    }
                    var time_out = setInterval(function() {
                        clear();
                        $('#wrapper_data').addClass('hidden');
                        $('#info').text('Silahkan scan tiket atau tap kartu anda');
                        clearInterval(time_out);
                    }, {{ config('uno.timeout_in') * 1000 }}); // 1 menit
                    if (datas.memberperiod) {
                        var memberperiod = datas.memberperiod || '';
                    }

                    var pesan = datas.pesan;
                    if (image) { setimage(image, 'imagein'); }
                    $('#posname').text(posname);
                    $('#posip').text(posip);
                    if (typeof datas.memberstatus !== "undefined") {
                        $('#memberstatus').text(memberstatus);
                    } else {
                        $('#memberstatus').text('');
                    }
                    $('#lpr').text(lpr);
                    $('#datecapture').text(datecapture);
                    console.log('pesan : ' + pesan);
                    $('#info').text(pesan);
                    var r = setInterval(function() {
                        hasResponse = hasResponse ? !hasResponse : hasResponse;
                        if (action == 2) {
                            clear();
                        }
                        clearInterval(r);
                        console.log('beres in action ' + action + ' sec : ' + sec);
                    }, {{ config('uno.timeout_in') * 1000 }});
                } catch (error) {
                    console.error("Error parsing JSON: ", error);
                }

            })
            .listen('.my-event-out', (e) => {
                $('#wrapper_data').removeClass('hidden');
                blink();
                var jsonString = e.message;
                var jsonString = e.message;
                
                var jsonObject = JSON.parse(jsonString);
                hasResponse = true;

                var datas = jsonObject;
                var action = datas.action;
                $('#promosi_operator').addClass('hidden');
                $('#imagein').removeClass('hidden');
                if (action == 3) {
                    $('#standby').addClass('hidden');
                    $('#page-out').removeClass('hidden');
                }
                // Render QR setelah elemen visible (hindari render saat hidden)

                var local_ip = datas.local_ip;
                var job = datas.job;
                var posname = datas.posname;
                var posip = datas.posip;
                var image = datas.image || '';
                var imagein = datas.imagein || '';
                if (lpr == '') {
                    lpr = datas.lpr
                    model = datas.model;
                    datecapture = datas.datecapture || '';
                    memberstatus = datas.memberstatus || '';
                }
                var memberperiod = datas.memberperiod || '';
                var nota = datas.nota;
                var plateno = datas.plateno;
                var total = datas.total;
                var vehicletype = datas.vehicletype;
                var inpos = datas.inpos;
                var intime = datas.intime || '';
                var outtime = datas.outtime || '';
                var duration = datas.duration;
                var pesan = datas.pesan;
                var done = false;
                if (action == 3) {
                    $('#statusOut').text('');
                    $('#image').addClass('hidden');
                    $('#qr-container').removeClass('hidden');
                    var qr = datas.qris;
                    var qrEl = document.getElementById('qr');
                    $('#wrapper_data').removeClass('hidden');
                    // requestAnimationFrame agar browser paint layout dulu, baru render QR
                    requestAnimationFrame(function() {
                        if (qrEl && qr) {
                            qrEl.innerHTML = '';

                            qrcodeInstance = new QRCode(qrEl, {
                                text: qr,
                                width: 300,
                                height: 300,
                                colorDark: '#000000',
                                colorLight: '#ffffff',
                                correctLevel: QRCode.CorrectLevel.M,
                                useSVG: false
                            });
                            fetch('/api/log-frontend', {
                                method: 'POST',
                                headers: {'Content-Type': 'application/json'},
                                body: JSON.stringify({message:  (function(){ var d = new Date(); var pad = function(n,l){ return String(n).padStart(l||2,'0'); }; return d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds()) + '.' + pad(d.getMilliseconds(),3); })() + ' | nota: ' + nota})
                            });
                        }
                    });
                    var i = 0;
                    $('#expired').text('Masa Berlaku : ' + (datas.expired || '').replace(/\\\//g, '/'));

                    var time_out = setInterval(function() {
                        clear_out();
                        $('#promosi_operator').removeClass('hidden');
                        $('#info').text('Silahkan scan tiket atau tap kartu anda');
                        clearInterval(time_out);
                    }, {{ config('uno.timeout_out') * 1000 }}); // 1 menit

                }
                
                if (image) { setimage(image, 'imagein'); }
                $('#info').text(pesan);
                $('#posname').text(posname);
                $('#posip').text(posip);
                $('#memberstatus').text('Masa Aktif Member : ' + memberperiod);
                $('#plate').text(plateno);
                $('#lpr').text(lpr);
                $('#datecapture').text(datecapture);
                $('#nota').text(nota);
                $('#total').text(formatRupiah(total));
                $('#vehicletype').text(vehicletype);
                $('#intime').text(intime);
                $('#outtime').text(outtime);
                $('#duration').text(duration);
                // $('#image').attr('src', imagein);
                // $('#imagein').attr('src', image);
                $('#video').addClass('hidden');
                $('#imagein').removeClass('hidden');
                $('#labelin').removeClass('hidden');

                
                if (action == 1) {
                    var t = setInterval(function() {
                        hasResponse = hasResponse ? !hasResponse : hasResponse;
                        action = 0;
                        clearInterval(t);
                    }, {{ config('uno.timeout_out') * 1000 }});

                }
                if (action == 4) {
                    if (imagein) { setimage(imagein, 'image'); }
                    var qrEl = document.getElementById('qr');
                    if (qrEl) { qrEl.innerHTML = ''; }
                    $('#qr-container').addClass('hidden');
                    $('#page-out').removeClass('hidden');
                    $('#standby').addClass('hidden');
                    $('#wrapper_data').removeClass('hidden');
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
                    setInterval(function() {
                        lpr = '';
                        model = '';
                        datecapture = '';
                        memberstatus = '';
                        clear_out();
                        clearInterval(t);

                    }, {{ config('uno.timeout_out') * 1000 }}); // 30 detik

                }

            });

        function clear() {
            $('#memberstatus').text('\t');
            $('#lpr').text('\t');
            $('#datecapture').text('\t');
            $('#wrapper_data').addClass('hidden');
            $('#promosi_operator').removeClass('hidden');
            $('#imagein').addClass('hidden');
            $('#imagein').removeAttr('src');
            $('#imagein').attr('src', `{{ asset('public/Logo_Operator.jpg') }}`);
            $('#image').attr('src', `{{ asset('public/out.jpg') }}`)
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
            $('#wrapper_data').addClass('hidden');
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
            // $('#image').removeAttr('src');
            // $('#imagein').removeAttr('src');
            // $('#image').attr('src', `{{ asset('public/out.jpg') }}`);
            // $('#imagein').attr('src', `{{ asset('public/in.jpg') }}`);
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
    </script>
@endpush
