@extends('welcome')
@section('contents')
    @php
        $landscape = false;

    @endphp
    <div class="{{ $landscape ? 'grid grid-cols-2 gap-2 flex-grow mt-2 mb-24 h-[735px]' : '' }}">
        <div class="flex flex-col bg-gray-300 text-white">
            @include('components.lpr')
        </div>
        <div id="wrapper" class="{{ $landscape ? 'h-[735px]' : 'h-[930px]' }} ">
            @include('components.in')
        </div>

    </div>
@endsection
@push('scripts')
    <script>
        var sec = 10 * 1000;
        var model = '';
        var lpr = '';
        var datecapture = '';
        var memberstatus = '';
        Pusher.logToConsole = true;
        var hasResponse = false;
        var pusher = new Pusher('{{ $setting->pusher_key }}', {
            cluster: 'mt1'
        });

        var channel = pusher.subscribe('my-channel');
        channel.bind('my-event-out', function(data) {
            try {
                hasResponse = true;

                var datas = data.data;
                var action = datas.action;
                
                console.log('=== PUSHER EVENT RECEIVED ===');
                console.log('Action:', action);
                
                // Delay semua operations sampai DOM benar-benar siap
                requestAnimationFrame(function() {
                    setTimeout(function() {
                        console.log('DOM ready, starting processing...');
                        
                        if (action == 3 || action == 4) {
                            console.log('Injecting out component');
                            var html = `@include('components.out')`;
                            var wrapperEl = document.getElementById('wrapper');
                            if (wrapperEl) {
                                wrapperEl.innerHTML = html;
                                console.log('Out component injected');
                            }
                        }
                        
                        var local_ip = data.local_ip;
                        var job = datas.job;
                        var posname = datas.posname;
                        var posip = datas.posip;
                        var image = datas.image;
                        var imagein = datas.imagein;
                        if (lpr == '') {
                            lpr = datas.lpr
                            model = datas.model;
                            datecapture = datas.datecapture;
                            memberstatus = datas.memberstatus;
                        }
                        var memberperiod = datas.memberperiod;
                        var nota = datas.nota;
                        var plateno = datas.plateno;
                        var total = datas.total;
                        var vehicletype = datas.vehicletype;
                        var inpos = datas.inpos;
                        var intime = datas.intime;
                        var outtime = datas.outtime;
                        var duration = datas.duration;
                        var pesan = datas.pesan;
                        var qr = datas.qris;
                        
                        console.log('Received QRIS:', qr ? qr.substring(0, 30) + '...' : 'NONE');
                        console.log('QRCode library available:', typeof QRCode !== 'undefined');
                        
                        // Delay QR generation lebih lama lagi
                        if (action == 3) {
                            console.log('Action 3 - preparing QR...');
                            setTimeout(function() {
                                console.log('Starting QR generation (2s delay)');
                                
                                var qrContainer = document.getElementById('qr-container');
                                var qrElement = document.getElementById("qr");
                                
                                console.log('QR Container found:', qrContainer !== null);
                                console.log('QR Element found:', qrElement !== null);
                                
                                if (qr && qr.trim() != '' && qrElement && qrContainer) {
                                    if (typeof QRCode !== 'undefined') {
                                        try {
                                            qrElement.innerHTML = '';
                                            var qrcode = new QRCode(qrElement, {
                                                text: qr,
                                                width: 200,
                                                height: 200,
                                                colorDark: "#000000",
                                                colorLight: "#ffffff",
                                                correctLevel: QRCode.CorrectLevel.H
                                            });
                                            qrContainer.style.display = 'flex';
                                            console.log('✓ QR Code generated and shown');
                                        } catch (e) {
                                            console.error('QRCode generation error:', e.message);
                                        }
                                    } else {
                                        console.error('QRCode library not available');
                                    }
                                } else {
                                    if (qrContainer) {
                                        qrContainer.style.display = 'none';
                                    }
                                    console.log('QRIS empty or elements not found');
                                }
                            }, 2000);
                        }
                        
                        // Set image dengan delay
                        setTimeout(function() {
                            console.log('Setting images...');
                            setimage(image, 'image');
                            setimage(imagein, 'imagein');
                            
                            try {
                                $('#info').text(pesan);
                                $('#posname').text(posname);
                                $('#qris-merchant').text(posname);
                                $('#posip').text(posip);
                                $('#memberstatus').text(memberstatus);
                                $('#lpr').text(lpr);
                                $('#datecapture').text(datecapture);
                                $('#nota').text(nota);
                                $('#total').text(formatRupiah(total));
                                $('#vehicletype').text(vehicletype);
                                $('#intime').text(intime);
                                $('#outtime').text(outtime);
                                $('#duration').text(duration);
                                console.log('DOM elements updated');
                            } catch (e) {
                                console.warn('DOM update error:', e);
                            }
                            
                            if (!qr || qr.trim() == '') {
                                var imgElement = document.getElementById('image');
                                if (imgElement) imgElement.style.display = 'block';
                                console.log('Showing image (no QR)');
                            } else {
                                var imgElement = document.getElementById('image');
                                if (imgElement) imgElement.style.display = 'none';
                                console.log('Hiding image (QR present)');
                            }
                        }, 1500);
                        
                        if (action == 1) {
                            console.log('Action 1 - resetting');
                            var t = setInterval(function() {
                                hasResponse = hasResponse ? !hasResponse : hasResponse;
                                clearInterval(t);
                            }, 15000);
                        }
                        
                        if (action == 4) {
                            console.log('Action 4 - goodbye message');
                            var balance = datas.balance;
                            try {
                                $('#informasi-pembayaran').text('Saldo : ' + formatRupiah(balance));
                            } catch (e) {
                                console.warn('Balance update error:', e);
                            }
                            var t = setInterval(function() {
                                var html = `@include('components.in')`;
                                var wrapperEl = document.getElementById('wrapper');
                                if (wrapperEl) {
                                    wrapperEl.innerHTML = html;
                                }
                                lpr = '';
                                model = '';
                                datecapture = '';
                                memberstatus = '';
                                clearInterval(t);
                            }, 15000);
                        }
                        
                    }, 100);
                });

            } catch (e) {
                console.error('Fatal error:', e.message);
            }

        });

        function clear() {
            $('#memberstatus').text('-');
            $('#lpr').text('-');
            $('#datecapture').text('-');
            $('#image').removeAttr('src');

            $('#image').attr('src', 'https://placehold.co/400x200')
            $('#info').text('Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.');
            lpr = '';
            model = '';
            datecapture = '';
            memberstatus = '';
        }

        function clear_out() {
            $('#memberstatus').text('-');
            $('#lpr').text('-');
            $('#datecapture').text('-');
            $('#nota').text('-');
            $('#total').text('-');
            $('#vehicletype').text('-');
            $('#intime').text('-');
            $('#outtime').text('-');
            $('#duration').text('-');
            $('#informasi-pembayaran').text(' ');
            $('#image').removeAttr('src');
            $('#imagein').removeAttr('src');
            $('#image').attr('src', 'https://placehold.co/400x200');
            $('#imagein').attr('src', 'https://placehold.co/400x200');
            $('#info').text('Silahkan scan tiket atau tap kartu anda');
            lpr = '';
            model = '';
            datecapture = '';
            memberstatus = '';
            // $('#info').text('Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.');
        }

        function formatRupiah(amount) {
            const formatter = new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            });
            return formattedAmount = formatter.format(amount);
        }
        // Set the video source
        // setVideo('\\\\192.168.9.223\\Share\\promosi.mp4');
    </script>
@endpush
