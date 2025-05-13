<div class="flex flex-col">
    <div class="bg-[{{ config('uno.style.primary') }}] border-4 border-black p-2">
        <span class=" text-xl text-white text-center align-center font-bold">IN</span>
        <div class="bg-[{{ config('uno.style.primary') }}] flex-grow text-center flex items-center justify-center">
            {{-- <img class="w-full h-[130px] object-contain" alt="" id="image"> --}}
            <img class="w-full h-[300px] object-contain" alt="" id="image">
        </div>
    </div>


    <div class="flex bg-[{{ config('uno.style.primary') }}]">
        <p class="text-white text-sm p-2 font-bold">
            <span id="nota">No Nota/No Plat In</span> <br>
            <span id="vehicletype">Jenis Kendaraan</span><br>
            <span id="intime">Jam masuk/ Pos Masuk</span> <br>
            <span id="outtime">Jam keluar/ Pos Keluar</span> <br>
        </p>
    </div>
    <div class="bg-[{{ config('uno.style.secondary') }}] p-4 font-bold  text-xl">
        <table class="w-full">
            <tr>
                <td class="text-left" width="150">Lama Parkir</td>
                <td>:</td>
                <td class="text-right text-2xl" id="duration">0 Jam 0 Menit</td>
            </tr>
            <tr>
                <td class="text-left"  width="150">Total</td>
                <td>:</td>
                <td class="text-right text-4xl" id="total">0</td>
            </tr>
            <tr id="informasi-pembayaran-row" class="hidden">
                <td class="text-left"  width="150" id="informasi-pembayaran-title">[Informasi Pembayaran]</td>
                <td>:</td>
                <td class="text-right text-4xl" id="informasi-pembayaran"></td>
            </tr>
            <tr id="informasi-member-row" class="hidden">
                <td class="text-left"  width="150" id="informasi-pembayaran-title">Masa Aktif Member</td>
                <td>:</td>
                <td class="text-right text-4xl" id="informasi-member"></td>
            </tr>
        </table>
    </div>
</div>
