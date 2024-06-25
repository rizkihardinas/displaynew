<?php

namespace App\Http\Controllers\Api;

use App\Events\InEvent;
use App\Events\OutEvent;
use App\Models\Security;
use Illuminate\Http\Request;
use App\Http\Traits\CryptAES;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

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
        if (is_null($request->password) || $request->password == '') {
            $response = [
                'userID' => $request->userID,
                'locationID' => $request->locationID,
                'daterequest' => $request->daterequest,
                'action' => $request->action,
                'data' => ['message' => 'Password Kosong']
            ];
            // $this->_requestUrl(json_encode($response),null,'DisplayApiRequest',103);
            return response()->json($response);
        }
        $password_req = $this->decrypt($request->password, $parameter);
        if ($password_req == false) {
            $response = [
                'userID' => $request->userID,
                'locationID' => $request->locationID,
                'daterequest' => $request->daterequest,
                'action' => $request->action,
                'data' => ['message' => 'Decrypt Password gagal']
            ];
            // $this->_requestUrl(json_encode($response),null,'DisplayApiRequest',103);
            return response()->json($response);
        }
        if ($username != $request->userId && $password != str_replace('"', '', $password_req)) {
            $response = [
                'userID' => $request->userID,
                'locationID' => $request->locationID,
                'daterequest' => $request->daterequest,
                'action' => $request->action,
                'data' => ['message' => 'Username atau password salah']
            ];
            // $this->_requestUrl(json_encode($response),null,'DisplayApiRequest',103);
            return response()->json($response);
        }
        if ($request->locationID != $kode_lokasi) {
            $response = [
                'userID' => $request->userID,
                'locationID' => $request->locationID,
                'daterequest' => $request->daterequest,
                'action' => $request->action,
                'data' => ['message' => 'Lokasi berbeda']
            ];
            // $this->_requestUrl(json_encode($response),null,'DisplayApiRequest',103);
            return response()->json($response);
        }
        $data = $this->decrypt($request->data, $parameter);
        $data = json_decode($data);
        $data->local_ip = getHostByName(getHostName());
        switch ($action) {
            case 1:
                $data->pesan = 'Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.';
                event(new InEvent($data));
                break;
            case 2:
                $data->pesan = 'Terima kasih, silahkan masuk.';
                event(new InEvent($data));
                break;
            case 3:
                $data->pesan = 'Silahkan melakukan pembayaran dengan E-Payment Card';
                event(new OutEvent($data));
                break;
            case 4:
                $data->pesan = 'Terima kasih atas kunjungan Anda, selamat jalan.';
                event(new OutEvent($data));
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
        // if ($mime) {
        //     if (file_exists($img)) {
        //         return response()->file($img, [
        //             'Content-Type' => $mime,
        //             'Content-Length' => filesize($img)
        //         ]);
        //     } else {
        //         return response()->json(['error' => 'File not found'], 404);
        //     }
        // } else {
        //     return response()->json(['error' => 'Invalid file type'], 400);
        // }
    }
    public function convertToBase64(Request $request)
    {
        $setting = Setting::first();
        $filePath = $request->input('i');
        $ip = $this->ip_extract($filePath);
        $filePath = str_replace('\\\\'.$ip.'\\','file:///'.$setting->path,$filePath);
        $filePath = str_replace('\\','/',$filePath);

        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $videoData = file_get_contents($filePath);
        $base64 = base64_encode($videoData);

        return response()->json(['base64' => $base64]);
    }
    function ip_extract($uncPath){

        if (preg_match('/\\\\\\\\([\d\.]+)\\\\/', $uncPath, $matches)) {
            $ipAddress = $matches[1];
            return $ipAddress;
        } else {
            return false;
        }
    }
}
