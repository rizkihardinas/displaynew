<div class="relative z-0 h-full overflow-hidden rounded-lg flex flex-col bg-green-400">
    <span class="text-5xl text-center font-bold p-4 ">TARIF PARKIR</span>
    <div class="mx-8 my-12 font-bold">
        <span class="text-5xl my-6">{{ $rate->vehicle }}</span>
        
            @php
                $prices = explode(';', $rate->price);
            @endphp
            <div class="flex justify-between text-4xl ">
                <span>1 Jam Pertama </span>
                <span>Rp {{ number_format($prices[0],0,',','.') }}</span>
            </div>
            <div class="flex justify-between text-4xl my-4">
                <span>1 Jam Berikutnya </span>
                <span>Rp {{ number_format($prices[1],0,',','.') }}</span>
            </div>
            <div class="flex justify-between text-4xl">
                <span>Denda Tiket Hilang </span>
                <span>Rp {{ number_format($rate->fine,0,',','.') }}</span>
            </div>
            <span class="mt-12 text-xl">* (Syarat dan ketentuan berlaku)</span>
            
        
    </div>

</div>
