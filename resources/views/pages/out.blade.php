@extends('welcome')
@section('contents')
    <div class="grid grid-cols-2 gap-2 flex-grow mt-2">
        <div class="flex flex-col bg-gray-300 text-white">
            @include('components.lpr')
        </div>
        <div class="flex flex-col">
            <div class="bg-[#04427B] flex-grow text-center p-2 flex items-center justify-center border-4 border-black">
                <img class="w-full h-full object-contain" alt="" id="imagein">
            </div>

            <div class="flex bg-[#04427B]">
                <p class="text-white text-lg p-4">
                    <span id="nota">No Nota/No Plat In</span> <br>
                    <span id="vehicletype">Jenis Kendaraan</span><br>
                    <span id="intime">Jam masuk/ Pos Masuk</span> <br>
                    <span id="outtime">Jam keluar/ Pos Keluar</span> <br>
                </p>
            </div>
            <div class="bg-[#f1ff00] p-4 font-bold  text-2xl">
                <div class="flex justify-between">
                    Lama Parkir : <span id="duration">0 Jam 0 Menit</span>
                </div>
                <div class="flex justify-between">
                    Total : <span class="text-4xl" id="total">10.000</span>
                </div>
                <div class="" id="informasi-pembayaran">
                    [Informasi Pembayaran]
                </div>

            </div>
        </div>
    </div>
@endsection
@push('scripts')
    <script>
        var sec = 10 * 1000;
        Pusher.logToConsole = true;
        var hasResponse = false;
        var pusher = new Pusher('db230015e67b085df02f', {
            cluster: 'mt1'
        });

        var channel = pusher.subscribe('my-channel');
        channel.bind('my-event-out', function(data) {
            hasResponse = true;
            
            var datas = data.data;
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
            
            if (datas.paymenttype) {
                var balance = datas.balance;
                $('#informasi-pembayaran').text('Saldo : ' + formatRupiah(balance));
            }
            setimage(image, 'image');
            setimage(imagein, 'imagein');
            $('#info').text(pesan);
            $('#posname').text(posname);
            $('#posip').text(posip);
            $('#memberstatus').text(memberstatus);
            $('#lpr').text(lpr);
            $('#datecapture').text(datecapture);
            $('#nota').text(nota);
            $('#total').text(formatRupiah(total));
            $('#vehicletype').text(vehicletype);
            $('#intime').text(intime);
            $('#outtime').text(outtime);
            $('#duration').text(duration);
            $('#image').attr('src', image);
            $('#imagein').attr('src', imagein);
            setInterval(function() {
                hasResponse = hasResponse ? !hasResponse : hasResponse;
            }, sec);

        });


        setInterval(function() {
            if (!hasResponse) {
                clear();
            }
        }, sec);

        function clear() {
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
            $('#info').text('Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.');
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
    </script>
@endpush
