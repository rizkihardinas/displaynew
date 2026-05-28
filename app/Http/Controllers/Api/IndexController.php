<?php

namespace App\Http\Controllers\Api;

use PDO;
use App\Events\InEvent;
use App\Models\Setting;
use App\Events\OutEvent;
use App\Models\Security;
use Illuminate\Http\Request;
use App\Http\Traits\CryptAES;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Http\Controllers\Controller;
use App\Models\Preference;
use Carbon\Carbon;

class IndexController extends Controller
{
    use CryptAES;
    function hit_display(Request $request)
    {
        $time = Carbon::now();
        Log::info('[Backend] '. $request->nota . '  | ' . $time->format('Y-m-d H:i:s.u'));
        $action = $request->action;
        $action = $request->action;
        $setting = app('setting');

        $security = app('security');
        $username = $security->username;
        $password = $security->password;
        $key = $security->key;
        $parameter = $key . \Carbon\Carbon::parse($request->daterequest)->format('ymd');
        $kode_lokasi =  $security->location;
        $isOk = true;
        $message = '';
        // if (is_null($request->password) || $request->password == '') {
        //     $isOk = false;
        //     $message = 'Password kosong';
        // }
        // $password_req = $this->decrypt($request->password, $parameter);
        // if ($password_req == false) {
        //     $isOk = false;
        //     $message = 'Decrypting password gagal';
        // }
        if (($username != $request->userId || $password != str_replace('"', '', $password_req)) == false) {
            $isOk = false;
            $message = 'username atau password tidak sama';
        }
        if (($request->locationID != $kode_lokasi)) {
            $isOk = false;
            $message = 'Kode lokasi berbeda';
        }
        if (!$isOk) {
            $response = [
                'userID' => $request->userID,
                'locationID' => $request->locationID,
                'daterequest' => $request->daterequest,
                'action' => $request->action,
                'data' => ['message' => $message]
            ];
            return response()->json($response);
        }
        try {
            $data = $this->decrypt($request->data, $parameter);
            $datas = json_decode($data);
            $datas->local_ip = $this->removeIp($request->ip());
            $datas->posip = $this->removeIp($datas->posip);

            $datas->datecapture = Carbon::createFromFormat('Y/m/d H:i:s', $datas->datecapture)->format('d/m/Y H:i:s');
            if (isset($data->memberperiod) && !empty($data->memberperiod)) {
                $datas->memberperiod = Carbon::createFromFormat('Y/m/d H:i:s', $datas->memberperiod)->format('d/m/Y H:i:s');
            }
            switch ($action) {
                case 1:
                    $datas->action = 1;
                    if ($datas->job == 'in' || $datas->job == 'IN') {
                        $datas->pesan = 'Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.';
                        event(new InEvent(json_encode($datas)));
                    } else {
                        $datas->pesan = 'Silahkan scan tiket atau tap kartu anda';
                        event(new OutEvent(json_encode($datas)));
                    }

                    break;
                case 2:
                    $datas->action = 2;
                    $datas->pesan = 'Terima kasih, silahkan masuk.';
                    event(new InEvent(json_encode($datas)));
                    break;
                case 3:
                    $datas->action = 3;
                    // Menggunakan locationID pada key cache agar tiap lokasi display bisa punya QRIS tersendiri
                    $cacheTicketKey = 'ticket_' . $request->locationID;
                    $cacheQrisKey   = 'qris_' . $request->locationID;

                    $savedTicket = cache()->get($cacheTicketKey);

                    $cacheImageKey    = 'image_' . $request->locationID;
                    $cacheImageinKey  = 'imagein_' . $request->locationID;

                    // Jika tiket berganti, update tiket di cache dan hapus cache lama
                    if (empty($savedTicket) || $savedTicket != $datas->nota) {
                        cache()->put($cacheTicketKey, $datas->nota, now()->addMinutes(60));
                        cache()->forget($cacheQrisKey);
                        cache()->forget($cacheImageKey);
                        cache()->forget($cacheImageinKey);
                    }

                    // Jika qris ada di request ini dan tidak kosong, simpan ke cache
                    if (isset($datas->qris) && $datas->qris != '') {
                        cache()->put($cacheQrisKey, $datas->qris, now()->addMinutes(60));
                    }
                    // Jika tidak ada di request, tapi di cache masih ada qris (berarti tiketnya sama), ambil dari cache
                    elseif (cache()->has($cacheQrisKey)) {
                        $datas->qris = cache()->get($cacheQrisKey);
                    }

                    // Simpan image ke cache jika ada
                    if (isset($datas->image) && $datas->image != '') {
                        $datas->image = $this->uncToUrl($datas->image);
                        cache()->put($cacheImageKey, $datas->image, now()->addMinutes(60));
                    } elseif (cache()->has($cacheImageKey)) {
                        $datas->image = cache()->get($cacheImageKey);
                    }

                    // Simpan imagein ke cache jika ada
                    if (isset($datas->imagein) && $datas->imagein != '') {
                        $datas->imagein = $this->uncToUrl($datas->imagein);
                        cache()->put($cacheImageinKey, $datas->imagein, now()->addMinutes(60));
                    } elseif (cache()->has($cacheImageinKey)) {
                        $datas->imagein = cache()->get($cacheImageinKey);
                    }

                    if (isset($datas->qris)) {
                        $payment = 'QRIS';
                        $expired = now()->addMinutes(10)->format('d/m/Y H:i:s');
                    } else {
                        $payment = 'E-Payment Card';
                        $expired = '';
                    }

                    $datas->pesan = 'Silahkan melakukan pembayaran ';
                    $datas->expired = $expired;
                    event(new OutEvent(json_encode($datas)));
                    break;
                case 4:
                    $cacheTicketKey   = 'ticket_' . $request->locationID;
                    $cacheQrisKey     = 'qris_' . $request->locationID;
                    $cacheImageKey    = 'image_' . $request->locationID;
                    $cacheImageinKey  = 'imagein_' . $request->locationID;
                    cache()->forget($cacheTicketKey);
                    cache()->forget($cacheQrisKey);
                    cache()->forget($cacheImageKey);
                    cache()->forget($cacheImageinKey);

                    $datas->qris = "";
                    $datas->action = 4;
                    $datas->pesan = 'Terima kasih atas kunjungan Anda, selamat jalan.';
                    event(new OutEvent(json_encode($datas)));
                    break;
                default:
                    $response = [
                        'userID' => $request->userID,
                        'locationID' => $request->locationID,
                        'daterequest' => $request->daterequest,
                        'action' => $request->action,
                        'data' => ['message' => 'Invalid action']
                    ];

                    return response()->json($response);
                    break;
            }
            $response = [
                'userID' => $request->userID,
                'locationID' => $request->locationID,
                'daterequest' => $request->daterequest,
                'action' => $request->action,
                'responsetime' => now()->diffInMilliseconds($time   ),
                'data' => $datas
            ];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = [
                'userID' => $request->userID,
                'locationID' => $request->locationID,
                'daterequest' => $request->daterequest,
                'action' => $request->action,
                'data' => ['message' => $th->getMessage(), 'file' => $th->getFile(), 'line' => $th->getLine()],
                'request' => json_encode($request->all())
            ];
            return response()->json($response);
            //throw $th;
        }
    }
    function doubleBackslashes($path)
    {
        // Gunakan preg_replace untuk menggantikan setiap \ dengan \\
        $pattern = '/\\\\/';
        $replacement = '\\\\\\\\'; // 4 backslashes karena setiap \\ harus di-escape dalam string
        $newPath = preg_replace($pattern, $replacement, $path);
        return $newPath;
    }
    function uncToUrl($uncPath)
    {
        $path = preg_replace('/^\\\\\\\\[\d\.]+\\\\image\\\\/', '', $uncPath);
        $path = str_replace('\\', '/', $path);
        return url('public/' . $path);
    }
    function generateImage(Request $request)
    {
        $img = $request->i;
        $ext = substr($img, -3);
        switch ($ext) {
            case 'jpg':
                $mime = 'image/jpeg';
                break;
            case 'gif':
                $mime = 'image/gif';
                break;
            case 'png':
                $mime = 'image/png';
                break;
            case 'mp4':
                $mime = 'video/mp4';
                break;
            default:
                $mime = false;
        }
        if ($mime && file_exists($img)) {
            header('Content-type: ' . $mime);
            header('Content-length: ' . filesize($img));

            $file = @fopen($img, 'rb');
            if ($file) {
                fpassthru($file);
                exit;
            }
        }
    }
    public function convertToBase64(Request $request)
    {
        $filePath = $request->i;
        if (env('IS_WINDOWS')) {
            $filePath = $filePath;
        } else {
            $setting = app('setting');

            $ip = $this->ip_extract($filePath);
            $filePath = str_replace('\\\\' . $ip . '\\image', 'file:///' . $setting->path, $filePath);
            $filePath = str_replace('\\', '/', $filePath);
            Log::info("File Path : " . $filePath);
            if (!file_exists($filePath)) {
                return response()->json(['error' => 'File not found'], 404);
            }
        }
        $videoData = file_get_contents($filePath);
        $base64 = base64_encode($videoData);

        return response()->json(['base64' => $base64]);
    }
    function ip_extract($uncPath)
    {

        if (preg_match('/\\\\\\\\([\d\.]+)\\\\/', $uncPath, $matches)) {
            $ipAddress = $matches[1];
            return $ipAddress;
        } else {
            return false;
        }
    }
    function setupSetting(Request $request)
    {
        DB::beginTransaction();
        try {
            $setting = Setting::first()->update($request->all());
            DB::commit();
            Cache::forget('setting_first');
            return response()->json($setting);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 200);
            //throw $th;
        }
    }
    function setupPreference(Request $request)
    {
        DB::beginTransaction();
        try {
            $setting = Preference::first()->update($request->all());
            DB::commit();
            return response()->json($setting);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 200);
            //throw $th;
        }
    }
    function setupSecurity(Request $request)
    {
        DB::beginTransaction();
        try {
            $security = Security::first()->update($request->all());
            DB::commit();
            Cache::forget('security_first');
            return response()->json($security);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json($th->getMessage(), 200);
            //throw $th;
        }
    }
    function removeIp($ip)
    {
        $segments = explode('.', $ip);

        // Ambil segmen ke-3 dan ke-4
        $filteredSegments = array_slice($segments, 2, 2);

        // Gabungkan kembali segmen tersebut
        $result = implode('.', $filteredSegments);
        return $result;
    }

    function logFrontend(Request $request)
    {
        Log::info('[Frontend] ' . $request->input('message', 'no message'));
        return response()->json(['status' => 'ok']);
    }
}
