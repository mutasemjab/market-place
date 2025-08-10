<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Country;
use App\Models\Notification;
use App\Models\User;
use App\Models\WholeSale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{


    public function register(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|unique:users,phone',
        ], [
            'name.required' => 'The name field is required.',
            'password.required' => 'The password field is required.',
            'phone.unique' => 'The phone has already been taken for the selected user type.',
            'email.unique' => 'The email has already been taken for the selected user type.',
        ]);

        DB::beginTransaction();
      //  try {
            $user = new User();
            $user->name = $request->get('name');
            $user->phone = $request->get('phone');
            $user->email = $request->get('email');
            $user->password = Hash::make($request->get('password'));

            if ($request->has('fcm_token')) {
                $user->fcm_token = $request->get('fcm_token');
            }

            $user->save();

            DB::commit();

           $accessToken = $user->createToken('authToken')->accessToken;
            return response(['user' => $user, 'token' => $accessToken], 200);
       // } catch (\Exception $e) {
            DB::rollBack();
            return response(['error' => 'Registration failed, please try again.'], 500);
       // }
    }



    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required:email',
            'password' => 'required|string',
        ], [
            'phone.required' => 'The phone field is required.',
            'password.required' => 'The password field is required.',
        ]);

        $user = null;

        $user = User::where('phone', $request->phone)
            ->first();


        if (!$user) {
            return response(["message" => "User not found."], 404);
        }

        if ($user->activate == 2) {
            return response(['errors' => ['Your account has been deactivated']], 403);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response(['message' => 'Invalid credentials'], 401);
        }

        $accessToken = $user->createToken('authToken')->accessToken;

        if (isset($request->fcm_token)) {
            $user->fcm_token = $request->fcm_token;
            $user->save();
        }

        return response(['user' => $user, 'token' => $accessToken], 200);
    }



    public function userProfile()
    {

        $user =  auth()->user();

        return response(['message' => ['User Get successfully'], 'user' => $user]);
    }

    public function updateProfile(Request $request)
    {


        $user =  auth()->user();

        if (isset($request->password)) {
            $user->password = Hash::make($request->password);
        }

        if ($request->has('photo')) {
            $the_file_path = uploadImage('assets/admin/uploads', $request->photo);
            $user->photo = $the_file_path;
        }

        if ($user->save()) {
            return response(['message' => ['Your setting has been changed'], 'user' => $user]);
        } else {
            return response(['errors' => ['There is something wrong']], 402);
        }
    }


    public function get_cities()
    {
        $cities = City::get();

        return response()->json(['data' => $cities]);
    }

    public function deleteAccount(Request $request)
    {
        $user =  auth()->user();

        if (isset($request->activate)) {
            $user->activate = 2;
        }

        if ($user->save()) {
            return response(['message' => ['Your Account deleted successfully']]);
        } else {
            return response(['errors' => ['There is something wrong']], 402);
        }
    }


    public function notifications()
    {
        $user = auth()->user();
        $notifications = Notification::orderBy('id', 'DESC')->get();
        return response(['data' => $notifications], 200);
    }
}
