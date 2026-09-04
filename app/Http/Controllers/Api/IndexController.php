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
            // $cacheActionKey    = 'action_' . $request->locationID;
            // if ($datas->action > cache()->get($cacheActionKey)) {
            //     cache()->put($cacheActionKey, $datas->action, now()->addMinutes(2));
            // } elseif (cache()->has($cacheActionKey)) {
            //     $datas->action = cache()->get($cacheActionKey);
            // }
            switch ($action) {
                case 1:
                    $datas->action = 1;
                    
                    if (strcasecmp($datas->job ?? '', 'in') === 0) {
                        $datas->image = $this->uncToUrl($datas->image);
                        $datas->pesan = 'Selamat datang, silahkan tekan tombol tiket atau tap kartu Anda.';
                        event(new InEvent(json_encode($datas)));
                    } else {
                        $locId = $request->locationID;
                        $cacheKeys = [
                            'ticket'  => 'ticket_' . $locId,
                            'qris'    => 'qris_' . $locId,
                            'total'   => 'total_' . $locId,
                            'lpr'     => 'lpr_' . $locId,
                            'image'   => 'image_' . $locId,
                            'imagein' => 'imagein_' . $locId,
                            'intime'  => 'intime_' . $locId,
                            'outtime' => 'outtime_' . $locId,
                        ];

                        foreach ($cacheKeys as $key) {
                            cache()->forget($key);
                        }

                        if (!empty($datas->image)) {
                            $datas->image = $this->uncToUrl($datas->image);
                        }
                        if (!empty($datas->imagein)) {
                            $datas->imagein = $this->uncToUrl($datas->imagein);
                        }

                        if (isset($datas->qris)) {
                            $payment = 'QRIS';
                            $expired = now()->addMinutes(10)->format('d/m/Y H:i:s');
                        } else {
                            $payment = 'E-Payment Card';
                            $expired = '';
                        }

                        $cacheData = array_filter([
                            $cacheKeys['qris']    => $datas->qris ?? null,
                            $cacheKeys['image']   => $datas->image ?? null,
                            $cacheKeys['total']   => $datas->total ?? null,
                            $cacheKeys['lpr']     => $datas->lpr ?? null,
                            $cacheKeys['imagein'] => $datas->imagein ?? null,
                            $cacheKeys['intime']  => $datas->intime ?? null,
                            $cacheKeys['outtime'] => $datas->outtime ?? null,
                        ], fn($val) => !is_null($val) && $val !== '');

                        $cacheData['vehicle_detected_' . $locId] = true;

                        cache()->putMany($cacheData, now()->addMinutes(2));

                        $datas->pesan = 'Silahkan scan tiket atau tap kartu anda';
                        event(new OutEvent(json_encode($datas)));
                    }
                    break;

                case 2:
                    $delayIn = (int) config('uno.delay_in', 0);
                    if ($delayIn > 0) {
                        sleep($delayIn);
                    }
                    $cacheImageKey   = 'image_' . $request->locationID;
                    $cacheImageinKey = 'imagein_' . $request->locationID;
                    $datas->action   = 2;

                    $cachePuts = [];
                    if (!empty($datas->image)) {
                        $datas->image = $this->uncToUrl($datas->image);
                        $cachePuts[$cacheImageKey] = $datas->image;
                    } else {
                        $cachedImg = cache()->get($cacheImageKey);
                        if ($cachedImg !== null) {
                            $datas->image = $cachedImg;
                        }
                    }

                    if (!empty($datas->imagein)) {
                        $datas->imagein = $this->uncToUrl($datas->imagein);
                        $cachePuts[$cacheImageinKey] = $datas->imagein;
                    } else {
                        $cachedImgIn = cache()->get($cacheImageinKey);
                        if ($cachedImgIn !== null) {
                            $datas->imagein = $cachedImgIn;
                        }
                    }

                    if (!empty($cachePuts)) {
                        cache()->putMany($cachePuts, now()->addMinutes(2));
                    }

                    $datas->pesan = 'Terima kasih, silahkan masuk.';
                    event(new InEvent(json_encode($datas)));
                    break;

                case 3:
                    $locId = $request->locationID;
                    $cacheVehicleDetectedKey = 'vehicle_detected_' . $locId;
                    if (!cache()->has($cacheVehicleDetectedKey)) {
                        $response = [
                            'userID'       => $request->userID,
                            'locationID'   => $request->locationID,
                            'daterequest'  => $request->daterequest,
                            'action'       => $request->action,
                            'responsetime' => now()->diffInMilliseconds($time),
                            'data'         => [
                                'message' => 'Kendaraan belum terdeteksi',
                                'pesan'   => 'Kendaraan belum terdeteksi'
                            ]
                        ];
                        return response()->json($response);
                    }
                    $datas->action = 3;

                    $cacheKeys = [
                        'ticket'  => 'ticket_' . $locId,
                        'qris'    => 'qris_' . $locId,
                        'total'   => 'total_' . $locId,
                        'lpr'     => 'lpr_' . $locId,
                        'image'   => 'image_' . $locId,
                        'imagein' => 'imagein_' . $locId,
                        'intime'  => 'intime_' . $locId,
                        'outtime' => 'outtime_' . $locId,
                    ];

                    $savedTicket = cache()->get($cacheKeys['ticket']);

                    if (empty($savedTicket) || $savedTicket != $datas->nota) {
                        cache()->put($cacheKeys['ticket'], $datas->nota, now()->addMinutes(2));
                        cache()->forget($cacheKeys['qris']);
                        cache()->forget($cacheKeys['total']);
                        cache()->forget($cacheKeys['image']);
                        cache()->forget($cacheKeys['imagein']);
                        cache()->forget($cacheKeys['intime']);
                        cache()->forget($cacheKeys['outtime']);
                        cache()->forget($cacheKeys['lpr']);
                    }

                    $cachePuts = [];

                    if (!empty($datas->qris)) {
                        $cachePuts[$cacheKeys['qris']] = $datas->qris;
                    } else {
                        $cached = cache()->get($cacheKeys['qris']);
                        if ($cached !== null) {
                            $datas->qris = $cached;
                        }
                    }

                    if (!empty($datas->image)) {
                        $datas->image = $this->uncToUrl($datas->image);
                        $cachePuts[$cacheKeys['image']] = $datas->image;
                    } else {
                        $cached = cache()->get($cacheKeys['image']);
                        if ($cached !== null) {
                            $datas->image = $cached;
                        }
                    }

                    if (!empty($datas->total)) {
                        $cachePuts[$cacheKeys['total']] = $datas->total;
                    } else {
                        $cached = cache()->get($cacheKeys['total']);
                        if ($cached !== null) {
                            $datas->total = $cached;
                        }
                    }

                    if (!empty($datas->lpr)) {
                        $cachePuts[$cacheKeys['lpr']] = $datas->lpr;
                    } else {
                        $cached = cache()->get($cacheKeys['lpr']);
                        if ($cached !== null) {
                            $datas->lpr = $cached;
                        }
                    }

                    if (!empty($datas->imagein)) {
                        $datas->imagein = $this->uncToUrl($datas->imagein);
                        $cachePuts[$cacheKeys['imagein']] = $datas->imagein;
                    } else {
                        $cached = cache()->get($cacheKeys['imagein']);
                        if ($cached !== null) {
                            $datas->imagein = $cached;
                        }
                    }

                    if (isset($datas->qris)) {
                        $payment = 'QRIS';
                        $expired = now()->addMinutes(10)->format('d/m/Y H:i:s');
                    } else {
                        $payment = 'E-Payment Card';
                        $expired = '';
                    }

                    if (!empty($datas->intime)) {
                        $cachePuts[$cacheKeys['intime']] = $datas->intime;
                    } else {
                        $cached = cache()->get($cacheKeys['intime']);
                        if ($cached !== null) {
                            $datas->intime = $cached;
                        }
                    }

                    if (!empty($datas->outtime)) {
                        $cachePuts[$cacheKeys['outtime']] = $datas->outtime;
                    } else {
                        $cached = cache()->get($cacheKeys['outtime']);
                        if ($cached !== null) {
                            $datas->outtime = $cached;
                        }
                    }

                    if (!empty($cachePuts)) {
                        cache()->putMany($cachePuts, now()->addMinutes(2));
                    }

                    $datas->pesan = 'Silahkan melakukan pembayaran ';
                    $datas->expired = $expired;
                    event(new OutEvent(json_encode($datas)));
                    break;

                case 4:
                    $locId = $request->locationID;
                    $cacheKeys = [
                        'ticket'   => 'ticket_' . $locId,
                        'qris'     => 'qris_' . $locId,
                        'total'    => 'total_' . $locId,
                        'lpr'      => 'lpr_' . $locId,
                        'image'    => 'image_' . $locId,
                        'imagein'  => 'imagein_' . $locId,
                        'intime'   => 'intime_' . $locId,
                        'outtime'  => 'outtime_' . $locId,
                    ];

                    if (empty($datas->total)) {
                        $cached = cache()->get($cacheKeys['total']);
                        if ($cached !== null) {
                            $datas->total = $cached;
                        }
                    }
                    if (empty($datas->lpr)) {
                        $cached = cache()->get($cacheKeys['lpr']);
                        if ($cached !== null) {
                            $datas->lpr = $cached;
                        }
                    }
                    if (!empty($datas->image)) {
                        $datas->image = $this->uncToUrl($datas->image);
                    } else {
                        $cached = cache()->get($cacheKeys['image']);
                        if ($cached !== null) {
                            $datas->image = $cached;
                        }
                    }

                    if (!empty($datas->imagein)) {
                        $datas->imagein = $this->uncToUrl($datas->imagein);
                    } else {
                        $cached = cache()->get($cacheKeys['imagein']);
                        if ($cached !== null) {
                            $datas->imagein = $cached;
                        }
                    }

                    if (empty($datas->intime)) {
                        $cached = cache()->get($cacheKeys['intime']);
                        if ($cached !== null) {
                            $datas->intime = $cached;
                        }
                    }

                    if (empty($datas->outtime)) {
                        $cached = cache()->get($cacheKeys['outtime']);
                        if ($cached !== null) {
                            $datas->outtime = $cached;
                        }
                    }

                    foreach ($cacheKeys as $key) {
                        cache()->forget($key);
                    }

                    cache()->forget('vehicle_detected_' . $locId);

                    $datas->qris = "";
                    $datas->action = 4;
                    $datas->pesan = 'Terima kasih atas kunjungan Anda, selamat jalan.';
                    event(new OutEvent(json_encode($datas)));
                    break;

                default:
                    $response = [
                        'userID'      => $request->userID,
                        'locationID'  => $request->locationID,
                        'daterequest' => $request->daterequest,
                        'action'      => $request->action,
                        'data'        => ['message' => 'Invalid action']
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
        return url('public/images/' . $path);
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
