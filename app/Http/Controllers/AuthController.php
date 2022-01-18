<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{

    public function username() {
        return 'username';
    }

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('username', 'password');

        if (! $token = Auth::guard('api')->attempt($credentials)) {
                error_log('deu erro');
               return response()->json(['error' => 'Unauthorized'], 401);
        }
        error_log('logou');
        return $this->respondWithToken($token);
    }

    public function register(Request $request) {
        error_log('Inicio do metodo de registros');
        $user = User::where('username', $request->username)->first();
        error_log('Primeira parte dos registros ok');
        if(!$user){
            $user = new User();
            $user->username = $request->username;
            $user->password = bcrypt($request->password);
            $user->activated = 1;
            //$user->fullname = $request->fullname;
            $user->fullname = "Nome de teste";
            $user->image = "";
            $user->save();
            error_log('CHegou aqui no registros');
            return response()->json(['message' => 'Usuario criado com sucesso.', 'user' => $user]);

        }else{
            error_log('Deu erro no registro');
            return response()->json(['message' => 'ERROR: Usuario ja cadastrado.'], 401);
        }
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'user' => auth('api')->user(),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
