<div class="px-2 py-1 h-full flex flex-col">
    {{-- <span class="bg-[{{ config('uno.style.primary') }}] my-1 text-2xl text-white px-1 font-bold hidden" id="labelin">OUT</span> --}}
    <div class="flex-grow bg-[{{ config('uno.style.primary') }}] flex items-center justify-center">
        @if (env('USE_LIVESTREAM'))
            <video id="video" autoplay controls data-url="" class="w-full h-auto">
                {{-- <source src="{{ asset('stream/stream.m3u8') }}" type="application/x-mpegURL">
            Your browser does not support the video tag. --}}
            </video>
        @else
            @if (!is_null($datas_operator))
                <div class="relative z-0 h-full w-full overflow-hidden rounded-lg flex flex-col bg-green-400"
                    data-carousel="slide" data-carousel-interval="{{ $setting->duration * 1000 }}" id="promosi_operator">
                    @foreach ($datas_operator as $key => $item)
                        <div class="{{ $key != 0 ? 'hidden' : '' }} duration-2000 ease-in-out absolute inset-0 transition-transform transform z-20 translate-x-0"
                            data-carousel-item="">
                            @if ($item['type'] == 'image')
                                @if ($item['hasEnc'])
                                    <img class="object-fill margin-0 w-full h-full"
                                        src="data:image/png;base64,{{ $item['path'] }}" alt="...">
                                @else
                                    <img class="object-fill margin-0 w-full h-full" src="{{ $item['path'] }}"
                                        alt="...">
                                @endif

                                {{-- <img class="object-fill margin-0 w-full h-full" src="{{ asset('1.jpg') }}" alt="..."> --}}
                            @else
                                <video src="data:video/mp4;base64,{{ $item['path'] }}" loop autoplay
                                    class="object-fill w-96 h-96" id="video"></video>
                            @endif

                        </div>
                    @endforeach
                </div>
            @endif
            <img class=" w-full h-96 object-contain hidden" alt="" id="imagein"
                src="{{ asset('public/Logo_Operator.jpg') }}">
        @endif


        {{-- <img class="w-full h-full object-contain" alt="" id="image" src="{{ request()->routeIs('out') ? asset('public/out.jpg') : asset('public/in.jpg') }}"> --}}
    </div>
    <div class="flex-none bg-[{{ config('uno.style.primary') }}] py-1 mt-1 text-center">
        <span class="text-3xl" id="plate">&nbsp;</span>
    </div>
    <div class="flex-none bg-[{{ config('uno.style.primary') }}] py-1 mt-1 text-center">
        <span class="text-xl" id="datecapture">&nbsp;</span>
    </div>
    <div class="flex-none bg-[{{ config('uno.style.primary') }}] py-1 mt-1 text-center">
        <span class="text-xl" id="memberstatus">&nbsp;</span>
    </div>
    <div class="flex-none bg-[{{ config('uno.style.secondary') }}] py-1 mt-1 text-center text-black" id="wrapper-info">
        <span class="text-2xl font-bold" id="info">&nbsp;</span>
    </div>
</div>


@push('scripts')
    @if (env('USE_LIVESTREAM'))
        <script src="https://cdn.jsdelivr.net/npm/hls.js@latest"></script>
        <script>
            var video = document.getElementById('video');
            var videoSrc = "{{ asset('public/stream/stream.m3u8') }}";

            if (Hls.isSupported()) {
                var hls = new Hls();
                hls.loadSource(videoSrc);
                hls.attachMedia(video);
                hls.on(Hls.Events.MANIFEST_PARSED, function() {
                    video.play();
                });
            } else if (video.canPlayType('application/vnd.apple.mpegurl')) {
                video.src = videoSrc;
                video.addEventListener('loadedmetadata', function() {
                    video.play();
                });
            }
        </script>
    @endif
@endpush
{{-- ffmpeg -v info -rtsp_transport tcp -i rtsp://sysuno:bdsyst3m@192.168.9.190:80/0 -c:v copy -c:a copy -maxrate 400k
-bufsize 1835k -pix_fmt yuv420p -flags -global_header -hls_time 10 -hls_list_size 6 -start_number 1 -fflags +genpts
-vsync public/stream/stream.m3u8 --}}
