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

            if ($getToken){
                $data = $jwt;
            }else{
                $decoded = JWT::decode($jwt, $this->key, ['HS256']);
                $data = $decoded;
            }

            $response = [
                'status' => 'success',
                'message' => 'Login correcto',
                'code' => 200,
                'data' => $data
            ];

        }else{

            $response = [
                'status' => 'error',
                'message' => 'Login incorrecto',
                'code' => 401
            ];
        }

        return $response;

    }

    public function checkToken($token, $identity = false)
    {
        $auth = false;
        try{
            $decoded = JWT::decode($token, $this->key, ['HS256']);
        }catch (\UnexpectedValueException $e){
            $auth = false;
        }catch (\DomainException $e){
            $auth = false;
        }

        if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;

        }else{
            $auth = false;
        }

        if ($identity){
            return $decoded;
        }

        return $auth;
    }
}
