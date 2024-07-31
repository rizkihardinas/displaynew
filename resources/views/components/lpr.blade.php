<div class="p-2 flex-grow flex flex-col">
    <div class="flex-grow bg-[#04427b] flex items-center justify-center">
        <img class="w-full h-full object-contain" alt="" id="image" src="{{ request()->routeIs('out') ? asset('public/out.jpg') : asset('public/in.jpg')    }}">
    </div>
    <div class="bg-[#04427b] p-2 mt-2 text-center">
        <span class="text-6xl" id="lpr">No. PLAT LPR</span>
    </div>
    <div class="bg-[#04427b] p-2 mt-2 text-center">
        <span class="text-3xl" id="datecapture">Hari, Tgl dan jam capture</span>
    </div>
    <div class="bg-[#04427b] p-2 mt-2 text-center">
        <span class="text-2xl" id="memberstatus">Member Info</span>
    </div>
    <div class="bg-[#f1ff00] p-2 mt-2 text-center text-black">
        <span class="text-3xl font-bold" id="info">Informasi bantuan, sesuai pesan UC</span>
    </div>
</div>