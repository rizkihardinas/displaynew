<div id="standby" class="relative z-0 h-full overflow-hidden rounded-lg flex flex-col {{ config('uno.style.primary') }}" data-carousel="slide" data-carousel-interval="{{ $setting->duration * 1000 }}">
    @foreach ($datas as $key => $item)
        <div class="{{$key <> 0 ? 'hidden' : ''}} duration-2000 ease-in-out absolute inset-0 transition-transform transform z-20 translate-x-0" data-carousel-item="">
            @if ($item['type'] == 'image')
                <img class="object-fill margin-0 w-full h-full" src="{{ $item['path'] }}" alt="...">
            @else
                <video src="{{ $item['path'] }}" loop autoplay muted playsinline class="w-full h-full object-fill block" id="video"></video>

            @endif

        </div>
    @endforeach
</div>

@push('scripts')
<script>
    $(window).on('load', function() {
        if (typeof FlowbiteInstances !== 'undefined') {
            const carousel = FlowbiteInstances.getInstance('Carousel', 'standby');
            if (carousel) {
                carousel._options.interval = {{ $setting->duration * 1000 }};
                carousel.pause();
                carousel.cycle();

                const el = carousel._el || carousel.el || document.getElementById('standby');
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.attributeName === 'class') {
                            const isHidden = el.classList.contains('hidden');
                            if (isHidden) {
                                carousel.pause();
                            } else {
                                carousel.pause();
                                carousel.cycle();
                            }
                        }
                    });
                });
                observer.observe(el, { attributes: true });
            }
        }
    });
</script>
@endpush