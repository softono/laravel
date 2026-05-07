<?php

namespace App\Http\Controllers;

use App\Helpers\General;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\TfaService;

class QrcodeController extends Controller
{
    /**
     * Display the front index page.
     *
     * @return \Illuminate\View\View
     */

    public function getModel(Request $request)
    {
        $id = $request->id;
        $userData = User::find(3);
        $data = (new TfaService())->generateTotpQrcode($userData->user_name);
        $secretKey = $data['secretKey'];
        $qrCode = $data['qrCode'];
        $qrKey = $secretKey;
        return view('common/verify_authenticator_modal', compact('secretKey', 'qrCode', 'qrKey', 'id'));
    }

    public function viewOtpModal(Request $request)
    {
        $secretKey = $request->secretKey;
        $id = $request->id;
        return view('common/verify_otp_modal', compact('secretKey', 'id'));
    }

    public function optVerifyProcess(Request $request)
    {
        $otp = str_replace(',', '', $request->otp_code);
        $secretKey = $request->secretKey;
        $data = (new General())->verifyTotp($secretKey, $otp);
        if ($data) {
            $userModel = User::find(auth()->user()->id);
            $userModel->google_auth_key = $secretKey;
            $userModel->google_auth_created = time();
            $backupCodes = [];
            for ($i = 0; $i < 5; $i++) {
                $backupCodes[] = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
            }

            $userModel->save();
            return response()->json(['status' => 1, 'message' => 'Verify successfully.', 'next' => 'refresh']);
        } else {
            return response()->json(['status' => 0, 'message' => 'Verify fail.']);
        }
    }

}
