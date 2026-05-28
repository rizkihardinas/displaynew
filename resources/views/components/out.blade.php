<div class="flex flex-col">
    <div class="{{ config('uno.style.primary') }} border-4 border-black p-2">
        <div class="{{ config('uno.style.primary') }} flex-grow text-center flex items-center justify-center">
            {{-- <img class="w-full h-[130px] object-contain" alt="" id="image"> --}}
            <div class="h-full flex items-stretch justify-center">
        
                <img 
                    class="w-full h-full object-cover"
                    alt=""
                    id="image"
                >

            </div>
            <div class="w-full flex flex-col items-center justify-center bg-white" id="qr-container">
                {{-- QRIS Template --}}
                <div class="w-full max-w-xs bg-white rounded shadow-md overflow-hidden border border-gray-200">
                    {{-- Header: QRIS + GPN logo --}}
                    <div class="flex items-center justify-between px-3 pt-3 pb-2 border-b border-gray-200">
                        <img src="{{ asset('assets/qris.png') }}" alt="QRIS" class="h-8 w-auto">
                        <img src="{{ asset('assets/gpn.svg') }}" alt="GPN" class="h-8 w-auto">
                    </div>
                    {{-- QR Code --}}
                    <div class="flex justify-center px-4 pb-2">
                        <div id="qr" class="border border-gray-300"></div>
                    </div>
                    {{-- Footer --}}
                </div>
                <span id="expired" class="text-red-500 font-bold mt-2"></span>
            </div>
        </div>
    </div>


    <div class="flex {{ config('uno.style.primary') }} p-2">
        <table class="text-black text-lg p-2 font-bold w-full">
            <tr>
                <td class="pr-2 py-0.5">No Nota/No Plat In</td>
                <td class="px-1">:</td>
                <td id="nota">-</td>
                 <td class="pr-2 py-0.5">Kendaraan</td>
                <td class="px-1">:</td>
                <td id="vehicletype">-</td>
            </tr>
            <tr>
                <td class="pr-2 py-0.5">Jam masuk</td>
                <td class="px-1">:</td>
                <td id="intime">-</td>
                <td class="pr-2 py-0.5">Jam keluar</td>
                <td class="px-1">:</td>
                <td id="outtime">-</td>
            </tr>
        </table>
    </div>
    <div class="{{ config('uno.style.primary') }} p-2 font-bold  text-xl">
        <table class="w-full">
            <tr>
                <td class="text-left" width="150">Lama Parkir</td>
                <td>:</td>
                <td class="text-right text-2xl" id="duration">0 Jam 0 Menit</td>
            </tr>
            <tr>
                <td class="text-left" width="150">Total</td>
                <td>:</td>
                <td class="text-right text-6xl" id="total">0</td>
            </tr>
            <tr id="informasi-pembayaran-row" class="hidden">
                <td class="text-left" width="150" id="informasi-pembayaran-title">[Informasi Pembayaran]</td>
                <td>:</td>
                <td class="text-right text-4xl" id="informasi-pembayaran"></td>
            </tr>
            <tr id="informasi-member-row" class="hidden">
                <td class="text-left" width="150" id="informasi-pembayaran-title">Masa Aktif Member</td>
                <td>:</td>
                <td class="text-right text-4xl" id="informasi-member"></td>
            </tr>
        </table>
    </div>
</div>
