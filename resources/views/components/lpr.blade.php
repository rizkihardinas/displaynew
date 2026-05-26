{{-- <div class="px-2 py-1 h-full flex flex-col"> --}}
@if (!is_null($datas_operator) && count($datas_operator) > 0)

    <div id="promosi_operator" class="w-full h-screen overflow-hidden bg-black">

        {{-- WRAPPER --}}
        <div class="flex w-full h-full gap-2">

            @foreach ($datas_operator as $key => $item)
                {{-- ITEM --}}
                <div class="flex-1 h-full overflow-hidden rounded-xl bg-black">

                    @if ($item['type'] == 'image')
                        @if ($item['hasEnc'])
                            <img src="data:image/png;base64,{{ $item['path'] }}" alt="promosi"
                                class="w-full h-full object-cover">
                        @else
                            <img src="{{ $item['path'] }}" alt="promosi" class="w-full h-full object-cover">
                        @endif
                    @else
                        @if (env('USE_LIVESTREAM'))
                            <video id="video-{{ $key }}" autoplay muted loop playsinline
                                class="w-full h-full object-cover">
                            </video>
                        @else
                            <video autoplay muted loop playsinline class="w-full h-full object-cover">

                                <source src="data:video/mp4;base64,{{ $item['path'] }}" type="video/mp4">

                            </video>
                        @endif
                    @endif

                </div>
            @endforeach

        </div>

    </div>

@endif
<div id="wrapper_data" class="hidden">
    <div class="flex-none {{ config('uno.style.primary') }} py-1 mt-1 text-center">
        <img class=" w-full h-96 object-contain hidden" alt="" id="imagein"
                src="{{ asset('Logo_Operator.jpg') }}">
    </div>
    <div class="flex-none {{ config('uno.style.primary') }} py-1 mt-1 text-center">
        <span class="text-3xl" id="lpr">&nbsp;</span>
    </div>
    <div class="flex-none {{ config('uno.style.primary') }} py-1 mt-1 text-center">
        <span class="text-xl" id="datecapture">&nbsp;</span>
    </div>
    <div class="flex-none {{ config('uno.style.primary') }} py-1 mt-1 text-center">
        <span class="text-xl" id="memberstatus">&nbsp;</span>
    </div>
    <div class="flex-none {{ config('uno.style.secondary') }} py-1 mt-1 text-center text-black" id="wrapper-info">
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
