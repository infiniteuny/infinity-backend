<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class SaweriaWebhookController extends Controller
{
    public function webhook(Request $request)
    {
        $data = $request->all();
        $amount = $data['amount_raw'];
        $email = $data['donator_email'];

        try {
            if ($amount >= 5000) {
                $user = User::where('email', $email)->first();
                $user->freepikDownloads()->update([
                    'limit_addons' => $user->freepikDownloads->limit_addons + 8,
                ]);
                return response()->json([
                    'status' => 'success',
                    'message' => 'Berhasil menambahkan kuota download freepik'
                ], 200);
            } else {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Donasi minimal Rp. 5.000'
                ], 400);
            }
        } catch (\Exception $th) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found',
            ], 404);
        }
    }
}
