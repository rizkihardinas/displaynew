<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Display API</title>
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/flowbite.min.css') }}" rel="stylesheet" />

    <style>
        @keyframes blink {

            0%,
            100% {
                background-color:
                    {{ config('uno.style.secondary') }}
                ;
                color: #000000;
            }

            50% {
                background-color: #f43f5e;
                color: #000000;
            }
        }

        .animate-blink {
            animation: blink 0.5s infinite;
        }
    </style>
</head>

<body class="h-screen flex flex-col {{ config('uno.style.background') }}">

    <!-- HEADER -->
    <div class="shrink-0 p-2 flex justify-between {{ config('uno.style.header') }} text-white">
        <div>
            @if (env('IS_WINDOWS'))
                <img src="{{ asset('Logo_Operator.jpg') }}" class="w-32 h-full" alt="">
            @else
                <img src="{{ asset('public/Logo_Operator.jpg') }}" class="w-32 h-full" alt="">
            @endif
        </div>
        <div class="text-right text-xl font-bold text-black">
            <div id="time"></div>
        </div>
    </div>

    <!-- CONTENTS -->
    <div class="flex-1 overflow-auto">
        <div class="p-4 h-full">
            @yield('contents')
        </div>
    </div>

    <!-- FOOTER -->
    <div
        class="shrink-0 flex justify-between items-center {{ config('uno.style.footer') }} text-black p-2 mt-2 font-bold">
        <div>
            <span class="text-xl">
                <img src="{{ env('IS_WINDOWS') ? asset('Logo_UNO.jpg') : asset('public/Logo_UNO.jpg') }}" class="w-24"
                    alt="">
            </span>
        </div>
        <div class="text-center flex-grow mx-24">
            <marquee class="text-2xl">{{ $setting->text_promotion ?? 'Promo text here' }}</marquee>
        </div>
        <div class="text-right">
            <div class="text-2xl text-black font-bold"><span id="posname">&nbsp;</span></div>
            <span class="text-md font-bold text-black" id="posip">{{ config('app.ip_server') }}</span>
        </div>
    </div>

    <script src="{{ asset('js/flowbite.min.js') }}"></script>
    @if (env('IS_WINDOWS'))
        <script src="{{ asset('js/app.js') }}"></script>
    @else
        <script src="{{ asset('public/js/app.js') }}"></script>
    @endif


    <script>
        $(document).on('click', 'body', function () {
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            } else if (document.documentElement.mozRequestFullScreen) { // Firefox
                document.documentElement.mozRequestFullScreen();
            } else if (document.documentElement.webkitRequestFullscreen) { // Chrome, Safari and Opera
                document.documentElement.webkitRequestFullscreen();
            } else if (document.documentElement.msRequestFullscreen) { // IE/Edge
                document.documentElement.msRequestFullscreen();
            }
        });

        function updateTime() {
            // Get the current time
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();

            // Format the time to be in HH:MM:SS format
            hours = hours < 10 ? '0' + hours : hours;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            seconds = seconds < 10 ? '0' + seconds : seconds;
            var formattedTime = now.getDate() + '/' + now.getMonth() + 1 + '/' + now.getFullYear() + ' ' + hours + ':' +
                minutes + ':' + seconds;

            // Update the clock div with the current time
            $('#time').text(formattedTime);
        }
        updateTime();

        // Update the time every second
        setInterval(updateTime, 1000);

        function setimage(img, attr) {
            axios({
                url: '{{ route('video.to.base64') }}',
                method: 'GET',
                params: {
                    i: img
                },
            })
                .then(function (response) {
                    const base64 = response.data.base64;
                    $('#' + attr).attr('src', 'data:image/png;base64,' + base64);
                })
                .catch(function (error) {
                    $('#' + attr).attr('src', '{{ asset('public/not-found.jpg') }}');
                    console.error('Error memuat video / image:', error);
                });
        }
    </script>

    @stack('scripts')
</body>

</html>