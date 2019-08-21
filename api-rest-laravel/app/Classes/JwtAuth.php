<?php

namespace App\Classes;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class JwtAuth
{

    protected $key;

    public function __construct()
    {
        $this->key = 'pajaroKeyBlacky';
    }

    public function signIn(array $inputs, $getToken = false)
    {
        $user = User::where(['email' => $inputs['email'], 'password' => hash('sha256',$inputs['password'])])->first();
        $signIn = !is_null($user) ? true : false;

        if ($signIn){
            $token = [
                'sub' => $user->id,
                'email' => $user->email,
                'name' => $user->name,
                'surname' => $user->surname,
                'iat' => time(),
                'exp' => time()*7*24
            ];

            $jwt = JWT::encode($token, $this->key, 'HS256');
            $decoded = JWT::decode($jwt, $this->key, ['HS256']);

            if ($getToken){
                $response = $jwt;
            }else{
                $response = $decoded;
            }

        }else{

            $response = [
                'status' => 'error',
                'message' => 'Login incorrecto',
                'code' => 401
            ];
        }

        return $response;

    }

}
