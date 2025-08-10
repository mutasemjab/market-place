<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email',
            'phone' => 'required|string|unique:users,phone',
            'password' => 'required|string|min:4|confirmed',
            'balance' => 'numeric|min:0',
            'total_points' => 'numeric|min:0',
            'google_id' => 'nullable|string',
            'activate' => 'required|in:1,2',
            'referal_code' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'balance' => $request->balance ?? 0,
                'total_points' => $request->total_points ?? 0,
                'google_id' => $request->google_id,
                'activate' => $request->activate,
                'referal_code' => $request->referal_code,
            ]);

            DB::commit();
            return redirect()->route('users.index')->with('success', __('messages.user_created_successfully'));

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', __('messages.error_creating_user') . ': ' . $e->getMessage());
        }
    }

  

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'required|string|unique:users,phone,' . $user->id,
            'password' => 'nullable|string|min:4|confirmed',
            'balance' => 'numeric|min:0',
            'total_points' => 'numeric|min:0',
            'google_id' => 'nullable|string',
            'activate' => 'required|in:1,2',
            'referal_code' => 'nullable|string|max:255',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'balance' => $request->balance ?? 0,
                'total_points' => $request->total_points ?? 0,
                'google_id' => $request->google_id,
                'activate' => $request->activate,
                'referal_code' => $request->referal_code,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            DB::commit();
            return redirect()->route('users.index')->with('success', __('messages.user_updated_successfully'));

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', __('messages.error_updating_user') . ': ' . $e->getMessage());
        }
    }

    public function destroy(User $user)
    {
        try {
            $user->delete();
            return redirect()->route('users.index')->with('success', __('messages.user_deleted_successfully'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('messages.error_deleting_user') . ': ' . $e->getMessage());
        }
    }

    public function toggleStatus(User $user)
    {
        try {
            $user->update([
                'activate' => $user->activate == 1 ? 2 : 1
            ]);
            
            $status = $user->activate == 1 ? __('messages.activated') : __('messages.deactivated');
            return response()->json([
                'success' => true, 
                'message' => __('messages.user_status_updated', ['status' => $status])
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => __('messages.error_updating_status')
            ]);
        }
    }

    public function adjustBalance(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'action' => 'required|in:add,subtract',
            'reason' => 'nullable|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $amount = $request->amount;
            if ($request->action === 'subtract') {
                if ($user->balance < $amount) {
                    return response()->json([
                        'success' => false,
                        'message' => __('messages.insufficient_balance')
                    ]);
                }
                $amount = -$amount;
            }

            $user->increment('balance', $amount);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => __('messages.balance_updated_successfully'),
                'new_balance' => $user->fresh()->balance
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => __('messages.error_updating_balance')
            ]);
        }
    }

    public function generateReferalCode()
    {
        do {
            $code = 'REF' . strtoupper(Str::random(6));
        } while (User::where('referal_code', $code)->exists());

        return response()->json(['code' => $code]);
    }
}

