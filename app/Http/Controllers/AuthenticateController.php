<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ExtendPack;
use App\Models\Pack;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class AuthenticateController extends Controller
{
    public function authenticate(Request $request)
    {
        // grab credentials from the request
        $credentials = $request->only('username', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = auth()->attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        if (auth()->user()->activated !== 1) {
            return response()->json(['error' => 'Tài khoản đã bị khóa']);
        }

        $u = User::find(auth()->user()->id);

        if ($u->role === config('constants.employee')) {
            $last_token = $u->remember_token;
            $u->remember_token = $token;
            $u->save();

            if ($last_token) {
                try {
                    $t = new \PHPOpenSourceSaver\JWTAuth\Token($last_token);
                    JWTAuth::manager()->invalidate($t, $forceForever = false);
                } catch (\Exception$e) {}
            }
        }

        $packs = Pack::select('code', 'duration', 'amount', 'price')->get();
        $extendPacks = ExtendPack::select('code')->get();
        $branches = Branch::select('id', 'name', 'display_name')->orderBy('id', 'asc')->get();

        return response()->json([
            'token' => $token,
            'user_info' => auth()->user(),
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'packs' => $packs,
            'extendPacks' => $extendPacks,
            'branches' => $branches,
        ]);
    }
}
