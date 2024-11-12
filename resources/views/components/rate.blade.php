<div class="relative z-0 h-full overflow-hidden rounded-lg flex flex-col bg-green-400">
    <span class="text-2xl text-center font-bold p-4 ">TARIF PARKIR</span>
    <div class="mx-8 my-12 font-bold">
        <span class="text-2xl my-6">{{ $rate->vehicle }}</span>
        
            @php
                $prices = explode(';', $rate->price);
            @endphp
            <div class="flex justify-between text-md ">
                <span>1 Jam Pertama </span>
                <span>Rp {{ number_format($prices[0],0,',','.') }}</span>
            </div>
            <div class="flex justify-between text-md my-4">
                <span>1 Jam Berikutnya </span>
                <span>Rp {{ number_format($prices[1],0,',','.') }}</span>
            </div>
            <div class="flex justify-between text-md">
                <span>Denda Tiket Hilang </span>
                <span>Rp {{ number_format($rate->fine,0,',','.') }}</span>
            </div>
            <span class="mt-12 text-sm">* (Syarat dan ketentuan berlaku)</span>
            
        
    </div>

</div>
