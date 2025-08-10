<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Coupon;

class BannerController extends Controller
{

    public function index()
    {
        $data = Banner::get();

        return response()->json(['data'=>$data]);
    }

}