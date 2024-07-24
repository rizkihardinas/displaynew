<div class="relative h-full overflow-hidden rounded-lg flex flex-col bg-green-400" data-carousel="slide" data-carousel-interval="{{ $setting->duration * 1000 }}">
    @foreach ($datas as $key => $item)
        <div class="{{$key <> 0 ? 'hidden' : ''}} duration-2000 ease-in-out" data-carousel-item="">
            @if ($item['type'] == 'image')
                <img class="object-fill margin-0 w-full h-full" src="data:image/png;base64,{{ $item['path'] }}" alt="...">
            @else
            <video src="data:video/mp4;base64,{{ $item['path'] }}" loop autoplay class="object-fill w-full h-full" id="video"></video>
            @endif

        </div>
    @endforeach
</div>