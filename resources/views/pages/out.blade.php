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
            hasResponse = true;

            var datas = data.data;
            var action = datas.action;
            if (action == 3 || action == 4) {
                var html = `@include('components.out')`;
                $('#wrapper').html(html);
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
            console.log('Received QRIS:', qr);
            console.log('QRCode library available:', typeof QRCode !== 'undefined');
            var done = false;
            if (action == 3) {
                var i = 0;
                // Gunakan setTimeout untuk memastikan DOM sudah siap sebelum membuat QR Code
                setTimeout(function() {
                    console.log('=== Starting QR Code Generation ===');
                    console.log('QRIS value:', qr);
                    console.log('QRIS length:', qr ? qr.length : 0);
                    console.log('QRIS is empty:', !qr || qr.trim() == '');
                    
                    if (qr && qr.trim() != '') {
                        try {
                            // Check if QRCode library is available
                            if (typeof QRCode === 'undefined') {
                                console.error('QRCode library not loaded!');
                                return;
                            }
                            
                            // Clear elemen terlebih dahulu jika ada
                            var qrElement = document.getElementById("qr");
                            console.log('QR Element found:', qrElement !== null);
                            
                            if (qrElement) {
                                qrElement.innerHTML = '';
                                console.log('Creating QRCode with text:', qr.substring(0, 50) + '...');
                                
                                var qrcode = new QRCode(qrElement, {
                                    text: qr,
                                    width: 200,
                                    height: 200,
                                    colorDark: "#000000",
                                    colorLight: "#ffffff",
                                    correctLevel: QRCode.CorrectLevel.H
                                });
                                
                                $('#expired').text('Masa Berlaku : ' + (datas.expired || ''));
                                $('#informasi-pembayaran-row').removeClass('hidden');
                                $('#qr-container').show();
                                console.log('✓ QR Code generated successfully');
                                console.log('✓ qr-container class:', $('#qr-container').attr('class'));
                            } else {
                                console.error('❌ Element #qr tidak ditemukan');
                                console.log('Available elements:', document.querySelectorAll('[id]').length);
                            }
                        } catch (e) {
                            console.error('❌ Error generating QR code:', e.message);
                            console.error('Stack:', e.stack);
                        }
                    } else {
                        $('#qr-container').hide();
                        console.log('⚠ QRIS string kosong atau tidak ada');
                    }
                }, 300);

                if (memberperiod) {
                    $('#informasi-pembayaran').text('masa aktif member : ' + memberperiod);
                }
                var time_out = setInterval(function() {
                    // clear_out();
                    var html = `@include('components.in')`;
                    $('#wrapper').html(html);
                    $('#info').text('Silahkan scan tiket atau tap kartu anda');
                    clearInterval(time_out);

                }, 15000); // 1 menit

            }

            setimage(image, 'image');
            setimage(imagein, 'imagein');
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
            
            // Handle image display - tampilkan image jika tidak ada QR
            if (!qr || qr.trim() == '') {
                $('#image').show();
                $('#qr-container').hide();
                console.log('Showing image (no QR)');
            } else {
                $('#image').hide();
                $('#qr-container').show();
                console.log('Hiding image (QR present)');
            }
            if (action == 1) {
                var t = setInterval(function() {
                    hasResponse = hasResponse ? !hasResponse : hasResponse;
                    action = 0;
                    clearInterval(t);
                }, 15000);

            }
            if (action == 4) {
                var balance = datas.balance;
                $('#informasi-pembayaran').text('Saldo : ' + formatRupiah(balance));
                var t = setInterval(function() {
                    $('#image').attr('src', `{{ asset('public/out.jpg') }}`);
                    $('#memberstatus').text('-');
                    $('#lpr').text('-');
                    $('#datecapture').text('-');
                    $('#imagein').attr('src', `{{ asset('public/in.jpg') }}`);
                    var html = `@include('components.in')`;
                    $('#wrapper').html(html);
                    lpr = '';
                    model = '';
                    datecapture = '';
                    memberstatus = '';
                    $('#info').text('Silahkan scan tiket atau tap kartu anda');
                    clearInterval(t);
                }, 15000); // 30 detik

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
