<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Traits\Setting;
use App\Models\Security;
use App\Models\Setting as ModelsSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class IndexController extends Controller
{
    function in(){
        $json = Storage::disk('public')->get('promotion.json');
        $promotions = json_decode($json);

        $setting = ModelsSetting::first();
        $promotion_text = $setting->text_promotion;
        $interval_standby = $setting->duration;
        $datas = [];
        foreach ($promotions->promotions as $key => $value) {
            $upac = [];
            $upac['path'] = $this->convertToBase64($value->path);
            $upac['type'] = $value->type;
            $datas[] = $upac;
        }
        $ip = getHostByName(getHostName());
        return view('pages.in',compact('datas','ip','setting'));
    }
    function out(){
        $setting = ModelsSetting::first();
        $json = Storage::disk('public')->get('promotion.json');
        $promotions = json_decode($json);
        $ip = getHostByName(getHostName());
        return view('pages.out',compact('ip','setting'));
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
}
