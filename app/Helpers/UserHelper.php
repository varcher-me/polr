<?php
namespace App\Helpers;

use App\Models\User;
use App\Helpers\CryptoHelper;
use Hash;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FA\Google2FA;

class UserHelper {
    public static $USER_ROLES = [
        'admin'    => 'admin',
        'default'  => '',
    ];

    public static function userExists($username) {
        /* XXX: used primarily with test cases */

        $user = self::getUserByUsername($username, $inactive=true);

        return ($user ? true : false);
    }

    public static function emailExists($email) {
        /* XXX: used primarily with test cases */

        $user = self::getUserByEmail($email, $inactive=true);

        return ($user ? true : false);
    }

    public static function validateUsername($username) {
        return ctype_alnum($username);
    }

    public static function userIsAdmin($username) {
        return (self::getUserByUsername($username)->role == self::$USER_ROLES['admin']);
    }

    public static function checkCredentials($username, $password) {
        $user = self::getUserByUsername($username);

        if ($user == null) {
            return false;
        }

        $correct_password = Hash::check($password, $user->password);

        if (!$correct_password) {
            return false;
        }
        else {
            return ['username' => $username, 'role' => $user->role];
        }
    }

    public static function checkMfa($username, $mfa)
    {
        $user = self::getUserByUsername($username);

        //User Not Existed
        if ($user == null) {
            return "NO_SUCH_USER";
        }

        //MFA Havn't Bind.
        if ($user->MFA_Token == null) {
            return "UNBIND MFA";
        }

        $google2fa = new Google2FA();
        $secretToken = $user->MFA_Token;
        $inputKey = $mfa;
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

        if ($valid) {
            return "MFA SUCCESS";
        } else {
            return "MFA FAILED";
        }
    }

    public static function setGoogleToken($username, $secretToken)
    {
        $user = self::getUserByUsername($username);
        $user->MFA_Token = $secretToken;
        $user->save();
    }


    public static function resetRecoveryKey($username) {
        $recovery_key = CryptoHelper::generateRandomHex(50);
        $user = self::getUserByUsername($username);

        if (!$user) {
            return false;
        }

        $user->recovery_key = $recovery_key;
        $user->save();

        return $recovery_key;
    }

    public static function userResetKeyCorrect($username, $recovery_key, $inactive=false) {
        // Given a username and a recovery key, return true if they match.
        $user = self::getUserByUsername($username, $inactive);

        if ($user) {
            if ($recovery_key != $user->recovery_key) {
                return false;
            }
        }
        else {
            return false;
        }
        return true;
    }

    public static function getUserBy($attr, $value, $inactive=false) {
        $user = User::where($attr, $value);

        if (!$inactive) {
            // if user must be active
            $user = $user
                ->where('active', 1);
        }

        return $user->first();
    }

    public static function getUserById($user_id, $inactive=false) {
        return self::getUserBy('id', $user_id, $inactive);
    }

    public static function getUserByUsername($username, $inactive=false) {
        return self::getUserBy('username', $username, $inactive);
    }

    public static function getUserByEmail($email, $inactive=false) {
        return self::getUserBy('email', $email, $inactive);
    }
}
