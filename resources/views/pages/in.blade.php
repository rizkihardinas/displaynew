@extends('welcome')
@section('contents')
    @php
        $landscape = true;

    @endphp
    <div class="{{ $landscape ? 'grid grid-cols-2 gap-2 flex-grow mt-2 mb-24 flex-grow' : '' }}">
        <div class="w-full h-full">
            @include('components.lpr')
        </div>
    {{-- <div id="wrapper" class="{{ $landscape ? 'h-[590px]' : 'h-[930px]' }} "> --}}
        <div id="wrapper" class="full">
            <div id="standby" class="w-full h-full">
                @include('components.in')
            </div>
            <div id="page-out" class="hidden w-full h-full">
                @include('components.out')
            </div>

        </div>

    </div>
@endsection
@push('scripts')
    <script>
        var sec = {{ (int)config('uno.timeout_in', 15) * 1000 }};
        var model = '';
        var lpr = '';
        var datecapture = '';
        var memberstatus = '';
        var hasResponse = false;
        var inTimer = null;
        window.Echo.channel('{{ strtolower(config('app.name')) }}_database_my-channel')
            .listen('.my-event', (e) => {
                if (inTimer) {
                    clearTimeout(inTimer);
                    inTimer = null;
                }

                try {
                    var datas;
                    if (typeof e.message === 'string') {
                        try {
                            datas = JSON.parse(e.message);
                        } catch (err) {
                            var escapedJsonString = e.message.replace(/\\/g, '\\\\');
                            datas = JSON.parse(escapedJsonString);
                        }
                    } else {
                        datas = e.message;
                    }

                    if (!datas) {
                        return;
                    }

                    hasResponse = true;
                    var local_ip = datas.local_ip || '';
                    var posname = datas.posname || '';
                    var image = datas.image ? datas.image.replace(/\\\\/g, '\\') : '';
                    var job = datas.job || '';
                    var action = datas.action || 0;
                    var posip = datas.posip || '';

                    blink(action);
                    $('#promosi_operator').addClass('hidden');
                    $('#wrapper_data').removeClass('hidden');
                    $('#imagein').removeClass('hidden');

                    if (lpr == '') {
                        lpr = datas.lpr || '';
                        model = datas.model || '';
                    }

                    datecapture = datas.datecapture ? datas.datecapture.replace(/\\/g, '') : '';
                    if (datas.memberstatus) {
                        var period = datas.memberperiod ? datas.memberperiod.replace(/\\/g, '') : '';
                        memberstatus = datas.memberstatus + (period ? ' - ' + period : '');
                        $('#memberstatus').text(memberstatus);
                    } else {
                        memberstatus = '';
                        $('#memberstatus').text('');
                    }

                    var pesan = datas.pesan || 'Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.';
                    if (image) {
                        setimage(image, 'imagein');
                    }
                    $('#posname').text(posname);
                    $('#posip').text(posip);
                    $('#lpr').text(lpr);
                    $('#info').text(pesan);

                    inTimer = setTimeout(function() {
                        clear();
                        console.log('beres in action ' + action + ' sec : ' + sec);
                    }, sec);
                } catch (error) {
                    console.error("Error processing IN event: ", error);
                }

            })
            .listen('.my-event-out', (e) => {

                var jsonString = e.message;
                var escapedJsonString = jsonString.replace(/\\/g, '\\\\');
                var jsonObject = JSON.parse(escapedJsonString);

                hasResponse = true;

                var datas = jsonObject;
                var action = datas.action;
                blink(action);
                $('#promosi_operator').addClass('hidden');
                $('#imagein').removeClass('hidden');
                if (action == 3 || action == 4) {
                    $('#standby').addClass('hidden');
                    $('#page-out').removeClass('hidden');
                }
                var local_ip = datas.local_ip;
                var job = datas.job;
                var posname = datas.posname;
                var posip = datas.posip;
                var image = datas.image.replace(/\\\\/g, '\\');
                var imagein = datas.imagein.replace(/\\\\/g, '\\');
                if (lpr == '') {
                    lpr = datas.lpr
                    model = datas.model;
                    datecapture = datas.datecapture.replace('\\', '').replace('\\', '');
                    memberstatus = datas.memberstatus;
                }
                var memberperiod = datas.memberperiod.replace('\\', '').replace('\\', '');
                var nota = datas.nota;
                var plateno = datas.plateno;
                var total = datas.total;
                var vehicletype = datas.vehicletype;
                var inpos = datas.inpos;
                var intime = datas.intime.replace('\\', '').replace('\\', '');
                var outtime = datas.outtime.replace('\\', '').replace('\\', '');
                var duration = datas.duration;
                var pesan = datas.pesan;
                var done = false;
                if (action == 3) {
                    var i = 0;
                    inTimer = setTimeout(function() {
                        clear_out();

                        $('#info').text('Silahkan scan tiket atau tap kartu anda');
                    }, {{ config('uno.timeout_out') * 1000 }}); // 1 menit

                }
                setimage(imagein, 'image');
                setimage(image, 'imagein');
                $('#info').text(pesan);
                $('#posname').text(posname);
                $('#posip').text(posip);
                $('#memberstatus').text('Masa Aktif Member : ' + memberperiod);
                $('#plate').text(plateno);
                // $('#datecapture').text(datecapture);
                $('#nota').text('Nota : ' + nota);
                $('#total').text(formatRupiah(total));
                $('#vehicletype').text('Jenis Kendaraan : ' + vehicletype);
                $('#intime').text('Tanggal Masuk : ' + intime);
                $('#outtime').text('Tanggal Keluar : ' + outtime);
                $('#duration').text(duration);
                // $('#image').attr('src', imagein);
                // $('#imagein').attr('src', image);
                $('#video').addClass('hidden');
                $('#imagein').removeClass('hidden');
                $('#labelin').removeClass('hidden');
                if (action == 1) {
                    inTimer = setTimeout(function() {
                        hasResponse = hasResponse ? !hasResponse : hasResponse;
                        action = 0;
                        clear_out();
                    }, {{ (int)config('uno.timeout_out', 15) * 1000 }});

                }
                if (action == 4) {
                    var balance = datas.balance;
                    $('#informasi-pembayaran-row').removeClass('hidden');
                    if (balance) {
                        $('#informasi-pembayaran').text('Saldo : ' + formatRupiah(balance));
                    } else {
                        $('#informasi-pembayaran-row').addClass('hidden');
                        $('#informasi-pembayaran').addClass('hidden');
                    }
                    var t = setInterval(function() {
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
            if (inTimer) {
                clearTimeout(inTimer);
                inTimer = null;
            }

            $('#memberstatus').text('\t');
            $('#lpr').text('\t');
            // $('#datecapture').text('\t');

            $('#wrapper_data').addClass('hidden');
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
            if (inTimer) {
                clearTimeout(inTimer);
                inTimer = null;
            }

            $('#memberstatus').text('\u00A0');
            $('#lpr').text('\u00A0');
            $('#plate').text('\u00A0');
            // $('#datecapture').text('\u00A0');
            $('#nota').text('');
            $('#total').text('');
            $('#vehicletype').text('');
            $('#intime').text('');
            $('#outtime').text('');
            $('#duration').text('');
            $('#informasi-pembayaran').text(' ');

            $('#wrapper_data').addClass('hidden');
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

        function blink(action) {
            $('#wrapper-info').addClass('animate-blink'+action);
            setTimeout(function() {
                $('#wrapper-info').removeClass('animate-blink'+action);
            }, 2000);
        }
        // Set the video source
        // setVideo('\\\\192.168.9.223\\Share\\promosi.mp4');
    </script>
@endpush
