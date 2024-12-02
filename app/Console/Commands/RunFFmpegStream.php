<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RunFFmpegStream extends Command
{
    protected $signature = 'stream:run-ffmpeg';
    protected $description = 'Run FFmpeg to convert RTSP stream to HLS';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // Perintah FFmpeg
        $command = [
            'ffmpeg',
            '-i', 'rtsp://sysuno:bdsyst3m@192.168.9.190:80/0',
            '-c:v', 'copy',
            '-hls_time', '2',
            '-hls_list_size', '5',
            '-f', 'hls',
            public_path('stream/playlist.m3u8')
        ];

        // Jalankan Proses FFmpeg
        // $process = new Process($command);
        $url = 'rtsp://sysuno:bdsyst3m@192.168.9.190:80/Stream';
        try {
            $path = base_path();
            // 
            $command = 'cd '.base_path().' && ffmpeg -v info -rtsp_transport tcp -i rtsp://'.config('ffmpeg.username').':'.config('ffmpeg.password').'@'.config('ffmpeg.host').':'.config('ffmpeg.port').'/0 -c:v copy -c:a copy -threads 10  -maxrate 400k -bufsize 1835k -pix_fmt yuv420p -flags -global_header -hls_time 2  -hls_list_size 2 -tune zerolatency -g 15  -start_number 1  -fflags +genpts -probesize 32 -flags low_delay -s 320x180 -b:v 240k -preset ultrafast -hls_flags delete_segments -an -r 15 public/stream/stream.m3u8';
            // $process->mustRun();
            shell_exec($command . ' 2>&1');
            // $this->info($command);
        } catch (ProcessFailedException $exception) {
            $this->error('Streaming failed: ' . $exception->getMessage());
        }
    }
}
