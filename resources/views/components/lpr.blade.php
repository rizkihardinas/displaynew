{{-- <div class="px-2 py-1 h-full flex flex-col"> --}}
<div class="relative z-0 h-full overflow-hidden rounded-lg flex flex-col bg-green-400" data-carousel="slide"
        data-carousel-interval="{{ $setting->duration * 1000 }}" id="promosi_operator">

        @foreach ($datas_operator as $key => $item)
            <div class="{{ $key != 0 ? 'hidden' : '' }}
                    duration-2000 ease-in-out absolute inset-0 transition-transform transform z-20 translate-x-0"
                data-carousel-item="">

                @if ($item['type'] == 'image')
                    @if ($item['hasEnc'])
                        <img class="object-fill margin-0 w-full h-full" src="data:image/png;base64,{{ $item['path'] }}"
                            alt="...">
                    @else
                        <img class="object-fill margin-0 w-full h-full" src="{{ $item['path'] }}" alt="...">
                    @endif
                @else
                    <video src="data:video/mp4;base64,{{ $item['path'] }}" loop autoplay muted playsinline
                        class="object-fill w-full h-full" id="video">

                    </video>
                @endif

            </div>
        @endforeach

    </div>
<div id="wrapper_data" class=" text-white hidden inset-0 z-10 flex flex-col {{ config('uno.style.secondary') }}">
    <div class="flex-none {{ config('uno.style.secondary') }} py-1text-center">
        <img class=" w-full h-full object-contain hidden" alt="" id="imagein"
            src="{{ asset('Logo_Operator.jpg') }}">
    </div>
    <div class="flex-none {{ config('uno.style.secondary') }} py-1 mt-2 text-center ">
        <span class="text-6xl font-bold" id="lpr">&nbsp;</span>
    </div>
    <div class="flex-none {{ config('uno.style.secondary') }} py-1 mt-1 text-center">
        <span class="text-xl" id="memberstatus">&nbsp;</span>
    </div>
    <div class="flex-none {{ config('uno.style.secondary') }} px-2 py-1 mt-1 text-center text-black" id="wrapper-info">
        <span class="text-3xl font-bold" id="info">&nbsp;</span>
    </div>
</div>
{{-- 
{{-- </div> --}}


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
