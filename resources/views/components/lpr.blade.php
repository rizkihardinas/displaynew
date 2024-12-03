<div class="px-2 py-1 h-72 flex flex-col">
    {{-- <span class="bg-[#04427b] my-1 text-2xl text-white px-1 font-bold hidden" id="labelin">OUT</span> --}}
    <div class="flex-1 bg-[#04427b] flex items-center justify-center">
        @if (env('USE_LIVESTREAM'))
            <video id="video" autoplay controls data-url="" class="w-full h-auto">
                {{-- <source src="{{ asset('stream/stream.m3u8') }}" type="application/x-mpegURL">
            Your browser does not support the video tag. --}}
            </video>
        @else
            <img class=" w-full h-96 object-contain" alt="" id="imagein" src="{{ asset('public/Logo_Operator.jpg') }}">
        @endif


        {{-- <img class="w-full h-full object-contain" alt="" id="image" src="{{ request()->routeIs('out') ? asset('public/out.jpg') : asset('public/in.jpg') }}"> --}}
    </div>
    <div class="bg-[#04427b] py-1 mt-1 text-center">
        <span class="text-3xl" id="plate">No. PLAT LPR</span>
    </div>
    <div class="bg-[#04427b] py-1 mt-1 text-center">
        <span class="text-xl" id="datecapture">Hari, Tgl dan jam capture</span>
    </div>
    <div class="bg-[#04427b] py-1 mt-1 text-center">
        <span class="text-xl" id="memberstatus">Member Info</span>
    </div>
    <div class="bg-[#f1ff00] py-1 mt-1 text-center text-black">
        <span class="text-xl font-bold" id="info">Informasi bantuan, sesuai pesan UC</span>
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
