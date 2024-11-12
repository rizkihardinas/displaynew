<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display API</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.css" rel="stylesheet" />
    {{-- @livewireStyles --}}
</head>

<body class=" bg-gray-200 h-dvh">
    <div class="mx-auto p-4 flex flex-col h-full">
        <div class="flex justify-between bg-[#94ceff] text-white p-2">
            <div>
                <div class="text-2xl text-black font-bold">POS : <span id="posname">NAMA POS</span></div>
                <div>
                    <span class="text-xl text-green-500 font-bold text-black " id="posip">IP</span>
                    <span class="text-xl font-bold text-black " id="posip">{{ $ip }}</span>
                </div>
            </div>
            <div class="text-right text-xl font-bold text-black">
                <div id="time">-</div>
                <div>Available parking</div>
            </div>
        </div>
        @yield('contents')
        
    </div>
    <div class="mx-4 mb-2 fixed bottom-0 left-0 right-0 flex justify-between items-center bg-[#94ceff] text-black p-2 mt-2 font-bold">
        <div><span class="text-xl"><img src="{{ asset('public/Logo_UNO.jpg') }}" class="w-24" alt=""></span>
        </div>
        <div class="text-center flex-grow">
            <marquee class="text-2xl">{{ $setting->text_promotion }}</marquee>
        </div>
        <div>Logo Operator</div>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"
    integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/flowbite/2.3.0/flowbite.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
@livewireScripts
@stack('scripts')
<script>
    $(document).on('click', 'body', function() {
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

        var formattedTime = now.getDate() + '/' + now.getMonth() + '/' + now.getFullYear() + ' ' + hours + ':' +
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
            .then(function(response) {
                const base64 = response.data.base64;
                $('#'+attr).attr('src','data:image/png;base64,' + base64);
            })
            .catch(function(error) {
                console.error('Error loading video:', error);
            });
    }

</script>

</html>
