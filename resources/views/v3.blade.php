<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Parking Display') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="{{ asset('css/flowbite.min.css') }}" rel="stylesheet" />
    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            overflow: hidden;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: #021C3F;
        }

        /* fluid scaling so layout fits 7"-15" screens without breakpoints */
        .screen {
            font-size: clamp(8px, 1.15vw, 24px);
        }

        @keyframes blink1 {
            0%, 100% {
                background-color: #F79743;
                color: #ffffff;
            }
            50% {
                background-color: #fcf803;
                color: #000000;
            }
        }

        .animate-blink {
            animation: blink1 0.5s infinite;
        }
    </style>
</head>
<body style="background:#021C3F;">
    @yield('contents')

    <script src="{{ asset('js/flowbite.min.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script src="{{ asset('js/qrcode.min.js') }}"></script>

    <script>
        $(document).on('click', 'body', function() {
            if (document.documentElement.requestFullscreen) {
                document.documentElement.requestFullscreen();
            } else if (document.documentElement.mozRequestFullScreen) {
                document.documentElement.mozRequestFullScreen();
            } else if (document.documentElement.webkitRequestFullscreen) {
                document.documentElement.webkitRequestFullscreen();
            } else if (document.documentElement.msRequestFullscreen) {
                document.documentElement.msRequestFullscreen();
            }
        });

        function setimage(img, attr) {
            $('#' + attr).attr('src', img);
        }

        @if(!empty($setting->warning_message))
        function checkInternetStatus() {
            var statusUrl = '{{ !empty($setting->ip_server) ? (preg_match("/^https?:\\/\\//i", $setting->ip_server) ? $setting->ip_server : "http://" . $setting->ip_server) . "/api/status" : "http://forwardapi.test/api/status" }}';

            $.ajax({
                url: statusUrl,
                type: 'GET',
                dataType: 'json',
                timeout: 3000,
                success: function(response) {
                    if (response && response.internet === 'up') {
                        $('#warning-container').addClass('hidden');
                    } else {
                        $('#warning-container').removeClass('hidden');
                    }
                },
                error: function() {
                    $('#warning-container').removeClass('hidden');
                }
            });
        }

        checkInternetStatus();
        setInterval(checkInternetStatus, {{ ((int)($setting->time_check_status ?? 5)) * 1000 }});
        @endif
    </script>

    @stack('scripts')
</body>
</html>
