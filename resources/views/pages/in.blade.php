@extends('welcome')
@section('contents')
    <div class="grid grid-cols-2 gap-2 flex-grow mt-2">
        <div class="flex flex-col bg-gray-300 text-white">
            @include('components.lpr')
        </div>
        <div class="relative h-auto overflow-hidden rounded-lg flex flex-col bg-green-400" data-carousel="slide" data-carousel-interval="{{ $setting->duration * 1000 }}">
            @foreach ($datas as $item)
                <div class="hidden duration-2000 ease-in-out" data-carousel-item="">
                    @if ($item['type'] == 'image')
                        <img class="object-fill margin-0 w-full h-full" src="data:image/png;base64,{{ $item['path'] }}" alt="...">
                    @else
                    <video src="data:video/mp4;base64,{{ $item['path'] }}" loop autoplay class="object-fill w-full h-full" id="video"></video>
                    @endif

                </div>
            @endforeach
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
        setInterval(function() {
            if (!hasResponse) {
                clear();
            }
        }, sec);

        function clear() {
            $('#memberstatus').text('-');
            $('#lpr').text('-');
            $('#datecapture').text('-');
            $('#image').removeAttr('src');
            $('#image').attr('src','https://placehold.co/600x400')
            $('#info').text('Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.');
        }

        // Set the video source
        // setVideo('\\\\192.168.9.223\\Share\\promosi.mp4');
    </script>
@endpush
