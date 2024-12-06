@extends('welcome')
@section('contents')
    @php
        $landscape = true;

    @endphp
    <div class="{{ $landscape ? 'grid grid-cols-2 gap-2 flex-grow mt-2 mb-24 h-[735px]' : '' }}">
        <div class="flex flex-col bg-gray-300 text-white">
            @include('components.lpr')
        </div>
        <div id="wrapper" class="{{ $landscape ? 'h-[590px]' : 'h-[930px]' }} ">
            @include('components.in')
        </div>

    </div>
@endsection
@push('scripts')
    <script>
        var sec = 10 * 1000;
        var model = '';
        var lpr = '';
        var datecapture = '';
        var memberstatus = '';
        var hasResponse = false;
        window.Echo.channel('{{ strtolower(config('app.name')) }}_database_my-channel')
            .listen('.my-event', (e) => {
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
                    if (lpr == '') {
                        lpr = datas.lpr
                        model = datas.model;
                        datecapture = datas.datecapture.replace('\\','').replace('\\','');
                        console.log('datas.datecapture : ' + datas.datecapture);
                        console.log('datas.datecapture replace : ' + datecapture);
                        memberstatus = datas.memberstatus + ' - ' + datas.memberperiod.replace('\\','').replace('\\','');
                    }


                    var time_out = setInterval(function() {
                        clear();
                        var html = `@include('components.in')`;
                        $('#wrapper').html(html);
                        $('#info').text('Silahkan scan tiket atau tap kartu anda');
                        clearInterval(time_out);
                    }, 15000); // 1 menit
                    var memberperiod = datas.memberperiod.replace('\\','').replace('\\','');
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
                    }, 10000);
                } catch (error) {
                    console.error("Error parsing JSON: ", error);
                }

            })
            .listen('.my-event-out', (e) => {


                var jsonString = e.message;
                var escapedJsonString = jsonString.replace(/\\/g, '\\\\');
                var jsonObject = JSON.parse(escapedJsonString);
                
                hasResponse = true;

                var datas = jsonObject;
                var action = datas.action;
                if (action == 3 || action == 4) {
                    var html = `@include('components.out')`;
                    $('#wrapper').html(html);
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
                    datecapture = datas.datecapture.replace('\\','').replace('\\','');
                    memberstatus = datas.memberstatus;
                }
                var memberperiod = datas.memberperiod.replace('\\','').replace('\\','');
                var nota = datas.nota;
                var plateno = datas.plateno;
                var total = datas.total;
                var vehicletype = datas.vehicletype;
                var inpos = datas.inpos;
                var intime = datas.intime.replace('\\','').replace('\\','');
                var outtime = datas.outtime.replace('\\','').replace('\\','');
                var duration = datas.duration;
                var pesan = datas.pesan;
                var done = false;
                if (action == 3) {
                    var i = 0;
                    var time_out = setInterval(function() {
                        clear_out();
                        var html = `@include('components.in')`;
                        $('#wrapper').html(html);
                        $('#video').removeClass('hidden');
                        $('#labelin').addClass('hidden');
                        // $('#imagein').addClass('hidden');
                        $('#info').text('Silahkan scan tiket atau tap kartu anda');
                        clearInterval(time_out);
                    }, 15000); // 1 menit

                }
                setimage(imagein, 'image');
                setimage(image, 'imagein');
                $('#info').text(pesan);
                $('#posname').text(posname);
                $('#posip').text(posip);
                $('#memberstatus').text(memberstatus);
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
                    }, 15000);

                }
                if (action == 4) {
                    var balance = datas.balance;
                    
                    if(balance){
                        $('#informasi-pembayaran').text('Saldo : ' + formatRupiah(balance));
                    }
                    var t = setInterval(function() {
                        $('#image').attr('src', `{{ asset('out.jpg') }}`);
                        console.log('clear boss');
                        $('#memberstatus').text('');
                        $('#lpr').text('');
                        $('#datecapture').text('');
                        $('#imagein').attr('src', `{{ asset('public/Logo_Operator.jpg') }}`);
                        var html = `@include('components.in')`;
                        $('#wrapper').html(html);
                        lpr = '';
                        model = '';
                        datecapture = '';
                        memberstatus = '';
                        $('#info').text('Silahkan scan tiket atau tap kartu anda');
                        clearInterval(t);
                        $('#video').removeClass('hidden');
                        $('#imagein').attr('src', `{{ asset('public/Logo_Operator.jpg') }}`);
                    }, 15000); // 30 detik

                }

            });

        function clear() {
            $('#memberstatus').text('   ');
            $('#lpr').text('   ');
            $('#datecapture').text('   ');
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
            $('#memberstatus').text('');
            $('#lpr').text('');
            $('#datecapture').text('');
            $('#nota').text('');
            $('#total').text('');
            $('#vehicletype').text('');
            $('#intime').text('');
            $('#outtime').text('');
            $('#duration').text('');
            $('#informasi-pembayaran').text(' ');
            $('#image').removeAttr('src');
            $('#imagein').removeAttr('src');
            $('#image').attr('src', `{{ asset('public/out.jpg') }}`);
            $('#imagein').attr('src', `{{ asset('public/in.jpg') }}`);
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
        // Set the video source
        // setVideo('\\\\192.168.9.223\\Share\\promosi.mp4');
    </script>
@endpush
