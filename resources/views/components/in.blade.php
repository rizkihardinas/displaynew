<div class="relative z-0 h-full overflow-hidden rounded-lg flex flex-col bg-green-400" data-carousel="slide" data-carousel-interval="{{ $setting->duration * 1000 }}">
    @foreach ($datas as $key => $item)
        <div class="{{$key <> 0 ? 'hidden' : ''}} duration-2000 ease-in-out absolute inset-0 transition-transform transform z-20 translate-x-0" data-carousel-item="">
            @if ($item['type'] == 'image')
                @if ($item['hasEnc'])
                <img class="object-fill margin-0 w-full h-full" src="data:image/png;base64,{{ $item['path'] }}" alt="...">    
                @else
                <img class="object-fill margin-0 w-full h-full" src="{{ $item['path'] }}" alt="...">    
                @endif
                
                {{-- <img class="object-fill margin-0 w-full h-full" src="{{ asset('1.jpg') }}" alt="..."> --}}
            @else
            <video src="data:video/mp4;base64,{{ $item['path'] }}" loop autoplay class="object-fill w-96 h-96" id="video"></video>
            @endif

        </div>
    @endforeach
</div>