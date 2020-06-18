<?php


namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
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
        $secretKey = null;
        try {
            $secretKey = $google2fa->generateSecretKey(64);
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
            $secretKey
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
            'key' => $secretKey,
            'QRCode' => $qrcode_image
        ]);
    }

    public function BindToken(Request $request)
    {
        var_dump($request);
    }
}