<div class="flex flex-col">
    <div class="{{ config('uno.style.primary') }} border-4 border-black p-2">
        <span class=" text-xl text-white text-center align-center font-bold" id="statusOut">QR Payment</span>
        <div class="{{ config('uno.style.primary') }} flex-grow text-center flex items-center justify-center">
            {{-- <img class="w-full h-[130px] object-contain" alt="" id="image"> --}}
            <img class="w-full h-[300px] object-contain" alt="" id="image">
            <div class="w-full h-[300px] flex flex-col items-center justify-center bg-white p-4" id="qr-container">
                <div id="qr"></div>
                <span id="expired" class="text-red-500 font-bold mt-2"></span>
            </div>
        </div>
    </div>


    <div class="flex {{ config('uno.style.primary') }}">
        <table class="text-white text-sm p-2 font-bold w-full">
            <tr>
                <td class="pr-2 py-0.5">No Nota/No Plat In</td>
                <td class="px-1">:</td>
                <td id="nota" class="font-normal">-</td>
            </tr>
            <tr>
                <td class="pr-2 py-0.5">Jenis Kendaraan</td>
                <td class="px-1">:</td>
                <td id="vehicletype" class="font-normal">-</td>
            </tr>
            <tr>
                <td class="pr-2 py-0.5">Jam masuk</td>
                <td class="px-1">:</td>
                <td id="intime" class="font-normal">-</td>
            </tr>
            <tr>
                <td class="pr-2 py-0.5">Jam keluar</td>
                <td class="px-1">:</td>
                <td id="outtime" class="font-normal">-</td>
            </tr>
        </table>
    </div>
    <div class="{{ config('uno.style.secondary') }} p-4 font-bold  text-xl">
        <table class="w-full">
            <tr>
                <td class="text-left" width="150">Lama Parkir</td>
                <td>:</td>
                <td class="text-right text-2xl" id="duration">0 Jam 0 Menit</td>
            </tr>
            <tr>
                <td class="text-left" width="150">Total</td>
                <td>:</td>
                <td class="text-right text-4xl" id="total">0</td>
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
