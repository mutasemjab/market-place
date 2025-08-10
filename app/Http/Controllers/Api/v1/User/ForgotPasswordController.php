<?php

namespace App\Http\Controllers\Api\v1\User;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ForgotPasswordController extends Controller
{

   public function resetPassword(Request $request)
    {
        $request->validate([
            'mobile' => 'required|exists:users,phone',
            'password' => 'required'
        ]);
    
        DB::beginTransaction();
        try {
            // Since validation already checks if the user exists, no need for an additional check
            $user = User::where('phone', $request->mobile)->first();
            $user->password = Hash::make($request->password);
            $user->save();
            
            DB::commit();
            return response(['message' => 'Password updated successfully', 'user' => $user], 200); // Use 200 for success
        } catch (Exception $e) {
            Log::info($e->getMessage());
            DB::rollBack();
            return response(['errors' => $e->getMessage()], 500); // Use 500 for server error
        }
    }

}
