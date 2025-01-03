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
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class IndexController extends Controller
{
    use CryptAES;
    function hit_display(Request $request)
    {
        $action = $request->action;

        $security = Security::first();
        $username = $security->username;
        $password = $security->password;
        $key = $security->key;
        $parameter = $key . \Carbon\Carbon::parse($request->daterequest)->format('ymd');
        $kode_lokasi =  $security->location;
        $isOk = true;
        $message = '';
        if (is_null($request->password) || $request->password == '') {
            $isOk = false;
            $message = 'Password kosong';
        }
        $password_req = $this->decrypt($request->password, $parameter);
        if ($password_req == false) {
            $isOk = false;
            $message = 'Decrypting password gagal';
        }
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
            $data = json_decode($data);
            $data->local_ip = $this->removeIp($request->ip());
            $data->posip = $this->removeIp($data->posip);

            $data->datecapture = Carbon::createFromFormat('Y/m/d H:i:s', $data->datecapture)->format('d/m/Y H:i:s');
            if (isset($data->memberperiod)) {
                $data->memberperiod = Carbon::createFromFormat('Y/m/d H:i:s', $data->memberperiod)->format('d/m/Y H:i:s');
            }

            switch ($action) {
                case 1:
                    $data->action = 1;
                    if ($data->job == 'in') {
                        $data->pesan = 'Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.';
                        event(new InEvent(json_encode($data)));
                    } else {
                        $data->pesan = 'Silahkan scan tiket atau tap kartu anda';
                        event(new OutEvent(json_encode($data)));
                    }

                    break;
                case 2:
                    $data->action = 2;
                    $data->pesan = 'Terima kasih, silahkan masuk.';
                    event(new InEvent(json_encode($data)));
                    break;
                case 3:
                    $data->action = 3;
                    $data->pesan = 'Silahkan melakukan pembayaran dengan E-Payment Card';
                    event(new OutEvent(json_encode($data)));
                    break;
                case 4:
                    $data->action = 4;
                    $data->pesan = 'Terima kasih atas kunjungan Anda, selamat jalan.';
                    event(new OutEvent(json_encode($data)));
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
                'data' => $data
            ];
            return response()->json($response);
        } catch (\Throwable $th) {
            $response = [
                'userID' => $request->userID,
                'locationID' => $request->locationID,
                'daterequest' => $request->daterequest,
                'action' => $request->action,
                'data' => ['message' => $th->getMessage()]
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
            $setting = Setting::first();

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
}
