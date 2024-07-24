@extends('welcome')
@section('contents')
    <div class="grid grid-cols-2 gap-2 flex-grow mt-2">
        <div class="flex flex-col bg-gray-300 text-white">
            @include('components.lpr')
        </div>
        <div id="wrapper" class="h-full">
            @include('components.in')
        </div>

    </div>
@endsection
@push('scripts')
    <script>
        var sec = 10 * 1000;
        Pusher.logToConsole = true;
        var hasResponse = false;
        var pusher = new Pusher('{{ $setting->pusher_key }}', {
            cluster: 'mt1'
        });

        var channel = pusher.subscribe('my-channel');
        channel.bind('my-event', function(data) {
            hasResponse = true;
            var datas = data.data;
            var local_ip = datas.local_ip;
            var posname = datas.posname;
            var image = datas.image;
            var job = datas.job;
            var posip = datas.posip;
            var lpr = datas.lpr;
            var model = datas.model;
            var datecapture = datas.datecapture;
            var memberstatus = datas.memberstatus;
            var memberperiod = datas.memberperiod;
            var pesan = datas.pesan;
            setimage(image, 'image');
            $('#posname').text(posname);
            $('#posip').text(posip);
            $('#memberstatus').text(memberstatus);
            $('#lpr').text(lpr);
            $('#datecapture').text(datecapture);
            $('#info').text(pesan);
            setInterval(function() {
                hasResponse = hasResponse ? !hasResponse : hasResponse;
            }, sec);

        });

        channel.bind('my-event-out', function(data) {
            hasResponse = true;

            var datas = data.data;
            var action = datas.action;
            if (action == 3 || action == 4) {
                var html = `@include('components.out')`;
                $('#wrapper').html(html);
            }
            var local_ip = data.local_ip;
            var job = datas.job;
            var posname = datas.posname;
            var posip = datas.posip;
            var image = datas.image;
            var imagein = datas.imagein;
            var lpr = datas.lpr;
            var model = datas.model;
            var datecapture = datas.datecapture;
            var memberstatus = datas.memberstatus;
            var memberperiod = datas.memberperiod;
            var nota = datas.nota;
            var plateno = datas.plateno;
            var total = datas.total;
            var vehicletype = datas.vehicletype;
            var inpos = datas.inpos;
            var intime = datas.intime;
            var outtime = datas.outtime;
            var duration = datas.duration;
            var pesan = datas.pesan;
            var done = false;
            if (action == 4 ) {
                var balance = datas.balance;
                $('#informasi-pembayaran').text('Saldo : ' + formatRupiah(balance));
                setInterval(function() {
                    clear_out();
                    
                }, sec);
                setInterval(function() {
                    var html = `@include('components.in')`;
                    $('#wrapper').html(html);
                    
                }, 30000); // 30 detik
                console.log('action 4 timer 10 detik');
            }
            if(action == 3){
                console.log('action 3 timer 10 detik');
                setInterval(function() {
                    clear_out();
                    var html = `@include('components.in')`;
                    $('#wrapper').html(html);
                    
                }, 25000); // 1 menit
            }
            setimage(image, 'image');
            setimage(imagein, 'imagein');
            $('#info').text(pesan);
            $('#posname').text(posname);
            $('#posip').text(posip);
            $('#memberstatus').text(memberstatus);
            $('#lpr').text(lpr);
            $('#datecapture').text(datecapture);
            $('#nota').text('Nota : ' + nota);
            $('#total').text(formatRupiah(total));
            $('#vehicletype').text('Jenis Kendaraan : ' + vehicletype);
            $('#intime').text('Tanggal Masuk : ' + intime);
            $('#outtime').text('Tanggal Keluar : ' + outtime);
            $('#duration').text(duration);
            $('#image').attr('src', image);
            $('#imagein').attr('src', imagein);
            if(action == 1){
                setInterval(function() {
                    hasResponse = hasResponse ? !hasResponse : hasResponse;
                }, 25000);
            }

        });

        function clear() {
            $('#memberstatus').text('-');
            $('#lpr').text('-');
            $('#datecapture').text('-');
            $('#image').removeAttr('src');
            $('#image').attr('src', 'https://placehold.co/400x200')
            // $('#info').text('Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.');
            $('#info').text(' ');
        }

        function clear_out() {
            $('#memberstatus').text('-');
            $('#lpr').text('-');
            $('#datecapture').text('-');
            $('#nota').text('-');
            $('#total').text('-');
            $('#vehicletype').text('-');
            $('#intime').text('-');
            $('#outtime').text('-');
            $('#duration').text('-');
            $('#informasi-pembayaran').text(' ');
            $('#image').removeAttr('src');
            $('#imagein').removeAttr('src');
            $('#image').attr('src', 'https://placehold.co/400x200')
            $('#imagein').attr('src', 'https://placehold.co/400x200')
            $('#info').text(' ');
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
