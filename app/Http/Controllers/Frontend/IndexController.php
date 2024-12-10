<?php

namespace App\Http\Controllers\Frontend;

use App\Events\InEvent;
use App\Http\Controllers\Controller;
use App\Http\Traits\Setting;
use App\Models\Promotion;
use App\Models\Security;
use App\Models\Setting as ModelsSetting;
use App\Models\Rate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    function in()
    {
        $promotions = Promotion::whereNull('is_operator')->get();
        $promotion_operators = Promotion::whereNotNull('is_operator')->get();
        $setting = ModelsSetting::first();
        $promotion_text = $setting->text_promotion;
        $interval_standby = $setting->duration;
        $datas = [];
        $datas_operator = [];


        foreach ($promotions as $key => $value) {
            $upac = $this->extractByOs($setting,$value);
            $datas[] = $upac;
        }

        foreach ($promotion_operators as $key => $value) {
            $upac = [];
            $upac = $this->extractByOs($setting,$value);
            $datas_operator[] = $upac;
        }
        $vehicle = isset($request->v) ? $request->v : Rate::where('is_default', 1)->first()->vehicle;
        $rate = Rate::where('vehicle', $vehicle)->first();
        $ip = getHostByName(getHostName());
        return view('pages.in', compact('datas', 'ip', 'setting', 'rate','datas_operator'));
    }

    function extractByOs(ModelsSetting $setting,$value)
    {
        $ip = $this->ip_extract($value->path);

        if (env('IS_WINDOWS')) {
            $filePath = str_replace('\\\\', '\\', $value->path);
            $upac['hasEnc'] = true;
            $upac['path'] = $this->convertToBase64($filePath);
            // $upac['path'] = $this->convertToBase64($value->path);
            $upac['type'] = $value->type;
        } else {
            $filePath = str_replace('\\\\' . $ip . '\\image', 'file:///' . $setting->path, $value->path);
            $filePath = str_replace('\\', '/', $filePath);
            Log::info($filePath);
            $upac['hasEnc'] = true;
            $upac['path'] = $this->convertToBase64($filePath);
            // $upac['path'] = $this->convertToBase64($value->path);
            $upac['type'] = $value->type;
        }

        return $upac;
    }
    public function convertToBase64($filePath)
    {
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        $videoData = file_get_contents($filePath);
        $base64 = base64_encode($videoData);

        return $base64;
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
    function test()
    {
        event(new InEvent(['message' => 'Hello from server!']));
    }
}
