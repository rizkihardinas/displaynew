@extends('welcome')
@section('contents')
    @php
        $landscape = true;

    @endphp
    <div class="{{ $landscape ? 'grid grid-cols-2 gap-2 flex-grow mt-2 mb-24 flex-grow' : '' }}">
        <div class="flex flex-col bg-gray-300 text-white">
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
        var sec = {{ config('uno.timeout_in') }} * 1000;
        var model = '';
        var lpr = '';
        var datecapture = '';
        var memberstatus = '';
        var hasResponse = false;
        window.Echo.channel('{{ strtolower(config('app.name')) }}_database_my-channel')
            .listen('.my-event', (e) => {
                blink();
                var jsonString = e.message;
                var escapedJsonString = jsonString.replace(/\\/g, '\\\\');
                try {
                    var jsonObject = JSON.parse(escapedJsonString);
                    hasResponse = true;
                    var datas = jsonObject;
                    var local_ip = datas.local_ip;
                    var posname = datas.posname;

                    var image = datas.image.replace(/\\\\/g, '\\');
                    var job = datas.job;
                    var action = datas.action;
                    var posip = datas.posip;

                    $('#promosi_operator').addClass('hidden');
                    $('#imagein').removeClass('hidden');
                    if (lpr == '') {
                        lpr = datas.lpr
                        model = datas.model;


                    }

                    datecapture = datas.datecapture.replace('\\', '').replace('\\', '');
                    if (datas.memberstatus) {
                        memberstatus = datas.memberstatus + ' - ' + datas.memberperiod.replace('\\', '').replace('\\',
                            '');
                    }
                    var time_out = setInterval(function() {
                        clear();
                        $('#info').text('Silahkan scan tiket atau tap kartu anda');
                        clearInterval(time_out);
                    }, {{ config('uno.timeout_in') * 1000 }}); // 1 menit
                    if (datas.memberperiod) {
                        var memberperiod = datas.memberperiod.replace('\\', '').replace('\\', '');
                    }

                    var pesan = datas.pesan;
                    setimage(image, 'imagein');
                    $('#posname').text(posname);
                    $('#posip').text(posip);
                    if (typeof datas.memberstatus !== "undefined") {
                        $('#memberstatus').text(memberstatus);
                    } else {
                        $('#memberstatus').text('');
                    }

                    $('#lpr').text(lpr);
                    $('#datecapture').text(datecapture);
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

                blink();
                var jsonString = e.message;
                var escapedJsonString = jsonString.replace(/\\/g, '\\\\');
                var jsonObject = JSON.parse(escapedJsonString);

                hasResponse = true;

                var datas = jsonObject;
                var action = datas.action;
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
                    var time_out = setInterval(function() {
                        clear_out();


                        $('#info').text('Silahkan scan tiket atau tap kartu anda');
                        clearInterval(time_out);
                    }, {{ config('uno.timeout_out') * 1000 }}); // 1 menit

                }
                setimage(imagein, 'image');
                setimage(image, 'imagein');
                $('#info').text(pesan);
                $('#posname').text(posname);
                $('#posip').text(posip);
                $('#memberstatus').text('Masa Aktif Member : ' + memberperiod);
                $('#plate').text(plateno);
                $('#datecapture').text(datecapture);
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
                    var t = setInterval(function() {
                        hasResponse = hasResponse ? !hasResponse : hasResponse;
                        action = 0;
                        clearInterval(t);
                    }, {{ config('uno.timeout_out') * 1000 }});

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
            $('#memberstatus').text('\t');
            $('#lpr').text('\t');
            $('#datecapture').text('\t');

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
