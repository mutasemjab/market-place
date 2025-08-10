<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
    public function index($type)
    {
        $data = Page::where('type',$type)->first();

        return response()->json(['data'=>$data]);
    }


}
