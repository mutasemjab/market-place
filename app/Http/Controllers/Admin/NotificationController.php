<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function create()
    {

        return view('admin.notifications.create');

    }

    public function send(Request $request){

        $this->validate($request,[
            'title'=>'required',
            'body'=>'required'
        ]);

        $response = FCMController::sendMessageToAll($request->title,$request->body);
            // Save the notification
            $noti = new Notification([
                'title' => $request->title,
                'body' => $request->body,
            ]);

         $noti->save();

        if($response){
            return redirect()->back()->with('message', 'Notification sent');
        }else{
            return redirect()->back()->with('error', 'Notification was not sent');
        }
    }
}
