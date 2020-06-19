<?php


namespace App\Http\Controllers;
use App\Helpers\UserHelper;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Illuminate\Http\Request;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;



class GoogleTokenController extends Controller
{
//    /**
//     * Create a new controller instance.
//     *
//     * @return void
//     */
//    public function __construct()
//    {
//        $this->middleware('auth');
//    }
//
//
//    public function index()
//    {
//        $user = auth()->user();
//        if (is_null($user->google_token)) {
//            return $this->showEnableTokenForm($user);
//        }
//        return view('app.profile.token.disable', ['user' => $user]);
//    }

    /**
     * Show Form with Key and QRCode for the User to enable it.
     *
     * @param $request
     * @return mixed
     */
    public function showBindTokenForm(Request $request)
    {
        $google2fa = new Google2FA();
        $username = $request->session()->get("username");
        $secretToken = null;
        try {
            $secretToken = $google2fa->generateSecretKey(64);
        } catch (IncompatibleWithGoogleAuthenticatorException $e) {
            abort(500, 'Incompatible With Google Authenticator.');
        } catch (InvalidCharactersException $e) {
            abort(500, 'Invalid Characters With Google Authenticator.');
        } catch (SecretKeyTooShortException $e) {
            abort(500, 'Google Authenticator SecretKey TooShort.');
        }
        $qrCodeUrl  = $google2fa->getQRCodeUrl(
            'GSJY.LTD',
            $username,
            $secretToken
        );

        $writer = new Writer(
            new ImageRenderer(
                new RendererStyle(200),
                new ImagickImageBackEnd()
            )
        );
        $qrcode_image = base64_encode($writer->writeString($qrCodeUrl));

        return view('mfa_token_bind', [
            'user' => $username,
            'token' => $secretToken,
            'QRCode' => $qrcode_image
        ]);
    }

    public function BindToken(Request $request)
    {
        $google2fa = new Google2FA();
        $userName = $request->session()->get("username");
        $secretToken = $request->input("SecretToken");
        $inputKey = $request->input("InputKey");
        $valid = false;

        try {
            $valid = $google2fa->verifyKey($secretToken, $inputKey, 2);
        } catch (IncompatibleWithGoogleAuthenticatorException $e) {
            abort(500, 'Incompatible With Google Authenticator.');
        } catch (InvalidCharactersException $e) {
            abort(500, 'Invalid Characters With Google Authenticator.');
        } catch (SecretKeyTooShortException $e) {
            abort(500, 'Google Authenticator SecretKey TooShort.');
        }

        if (!$valid) {
            return redirect(route('token_bind_prompt'))->with('error', 'Your MFA verify failed, please check.');
        }

        UserHelper::setGoogleToken($userName, $secretToken);
        return redirect()->route('index');
    }
}