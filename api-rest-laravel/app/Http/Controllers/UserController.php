<?php

namespace App\Http\Controllers;

use App\Classes\JwtAuth;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    protected $jwtAuth;

    public function __construct ()
    {
        $this->jwtAuth = new JwtAuth();
    }

    /**
     * Register a user
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        $data = [
            'status' => 'error',
            'code' => 500,
            'message' => 'El usuario no se registro correctamente'
        ];

        $validator = Validator::make($request->toArray(), [
            'name' => ['required', 'string', 'max:50', 'alpha'],
            'surname' => ['required', 'string', 'max:100', 'alpha'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        if($validator->fails()) {
            $data = [
                'status' => 'error',
                'code' => 401,
                'message' => 'El usuario no se registro correctamente',
                'errors' => $validator->errors()
            ];

        } else {

            $inputs = $request->toArray();
            $inputs['role'] = "ROLE_USER";
            $inputs['description'] = "Usuario nuevo";
            $inputs['image'] = "";
            $inputs['password'] = hash('sha256',$inputs['password']);
            unset($inputs['password_confirmation']);
            $inputs = array_map(
                'trim', $inputs
            );

            $user = User::create($inputs);
            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'El usuario se registro correctamente',
                'user_id' => $user->id
            ];
        }

        return response()->json($data,$data['code']);
    }

    /**
     * Login a user
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        //

        $data = [
            'status' => 'error',
            'code' => 500,
            'message' => 'El usuario no se logueo correctamente'
        ];

        $validator = Validator::make($request->toArray(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if($validator->fails()) {
            $data = [
                'status' => 'error',
                'code' => 401,
                'message' => 'Los parametros de login estan incompletos',
                'errors' => $validator->errors()
            ];

        }

        $inputs = $request->only('email','password');

        $data = $this->jwtAuth->signIn($inputs,true);

        return response()->json($data,$data['code']);

    }

    public function update(Request $request)
    {
        $token = $request->header('Authorization');
        $checkToken = $this->jwtAuth->checkToken($token);

        if ($checkToken){

            $validator = Validator::make($request->toArray(), [
                'name' => ['nullable', 'string', 'max:50', 'alpha'],
                'surname' => ['nullable', 'string', 'max:100', 'alpha'],
                'description' => ['nullable', 'string', 'max:255'],
                'image' => ['nullable', 'string', 'max:255'],
            ]);

            if($validator->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 401,
                    'message' => 'Los datos son erroneos',
                    'errors' => $validator->errors()
                ];

            }

            $inputs = $request->except('email','role','password');
            $identity = $this->jwtAuth->checkToken($token,true);
            $user = User::findOrFail($identity->sub)->update($inputs);

            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'El usuario se actualizo correctamente',
                'user_id' => $identity->sub
            ];

        }else{
            $data = [
              'status' => 'error',
              'code' => 401,
              'message' => 'NO Autorizado.'
            ];
        }

        return response()->json($data,$data['code']);
    }

    public function upload(Request $request)
    {
        $data = [
            'status' => 'error',
            'code' => 501,
            'message' => 'Error al subir imagen'
        ];

        return response()->json($data,$data['code']);
    }

}
