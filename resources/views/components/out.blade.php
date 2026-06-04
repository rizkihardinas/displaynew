<div class="flex flex-col hidden {{ config('uno.style.secondary') }}" id="page-out">
    <div class=" border-4 border-black p-2">
        <div class="{{ config('uno.style.secondary') }} flex-grow text-center flex items-center justify-center">
            {{-- <img class="w-full h-[130px] object-contain" alt="" id="image"> --}}
            <img class="w-full h-full object-contain" alt="" id="image">
            <div class="w-full flex flex-col items-center justify-center bg-white py-12" id="qr-container">
                {{-- QRIS Template --}}
                <div class="w-full max-w-xs bg-white  overflow-hidden">
                    {{-- Header: QRIS + GPN logo --}}
                    <div class="flex items-center justify-between pb-2 border-b border-gray-200">
                        <img src="{{ asset('assets/qris.png') }}" alt="QRIS" class="h-8 w-auto">
                        <img src="{{ asset('assets/gpn.svg') }}" alt="GPN" class="h-8 w-auto">
                    </div>
                    {{-- QR Code --}}
                    <div class="flex justify-center px-4 pb-2">
                        <div id="qr" class="border border-gray-300"></div>
                    </div>
                    {{-- Footer --}}
                </div>
                <span id="expired" class="text-red-500 font-bold my-6 text-2xl"></span>
            </div>
        </div>
    </div>


    <div class="flex {{ config('uno.style.secondary') }} p-2">
        <table class="{{ config('uno.style.text_secondary') }} text-2xl p-2 font-bold w-full">
            <tr>
                <td class="pr-2 py-0.5">No Nota</td>
                <td class="px-1">:</td>
                <td id="nota">-</td>
                <td class="pr-2 py-0.5">Kendaraan</td>
                <td class="px-1">:</td>
                <td id="vehicletype">-</td>
            </tr>
            <tr>
                <td class="pr-2 py-0.5">In</td>
                <td class="px-1">:</td>
                <td id="intime">-</td>
                <td class="pr-2 py-0.5">Out</td>
                <td class="px-1">:</td>
                <td id="outtime">-</td>
            </tr>
        </table>
    </div>
    <div class="flex flex-col flex-grow {{ config('uno.style.secondary') }} p-2 font-bold text-4xl {{ config('uno.style.text_secondary') }}">
        <table class="w-full">
            <tr>
                <td class="text-left" width="200">Lama Parkir</td>
                <td>:</td>
                <td class="text-right text-6xl" id="duration">0 Jam 0 Menit</td>
            </tr>
        </table>
        <table class="w-full mt-8">
            <tr>
                <td class="text-left" width="200">Total</td>
                <td>:</td>
                <td class="text-right" style="font-size: 10rem" id="total">0</td>
            </tr>
        </table>
    </div>
</div>
