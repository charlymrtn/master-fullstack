<?php

namespace App\Http\Controllers;

use App\Classes\JwtAuth;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
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
            User::findOrFail($identity->sub)->update($inputs);

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
        $image = $request->file('file0');
        $pass = true;

        $validator = Validator::make($request->all(),[
            'file0' => 'required|image|mimes:png,jpeg,jpg,gif'
        ]);

        if ($validator->fails()){
            $data = [
                'status' => 'error',
                'code' => 501,
                'message' => 'Tipo de archivo invalido'
            ];
            $pass = false;
        }
        if ($pass){
            if ($image){
                $image_name = time().$image->getClientOriginalName();
                $image_uploaded = Storage::disk('users')->put($image_name, File::get($image));
                if ($image_uploaded){
                    $data = [
                        'status' => 'success',
                        'code' => 200,
                        'message' => 'Imagen cargada correctamente',
                        'image' => $image_name
                    ];
                    $token = $request->header('Authorization');
                    $checkToken = $this->jwtAuth->checkToken($token,true);
                    User::findOrFail($checkToken->sub)->update(['image' => $image_name]);

                }else{
                    $data = [
                        'status' => 'error',
                        'code' => 501,
                        'message' => 'Error al subir imagen'
                    ];
                }


            }else{
                $data = [
                    'status' => 'error',
                    'code' => 501,
                    'message' => 'Error al subir imagen'
                ];
            }
        }

        return response()->json($data,$data['code']);
    }

    public function getImage(string $filename)
    {

        if(empty($filename) || is_null($filename) || !isset($filename)) {
            $data = [
                'status' => 'error',
                'code' => 401,
                'message' => 'nombre de archivo incorrecto'
            ];
        }

        if (Storage::disk('users')->exists($filename)){
            $image = Storage::disk('users')->get($filename);
            return new Response($image,200);

        } else {

            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'archivo no existe'
            ];

        }

        return response()->json($data,$data['code']);
    }

    public function getProfile(int $id)
    {

        if(empty($id) || is_null($id) || !isset($id) || !is_numeric($id)) {
            $data = [
                'status' => 'error',
                'code' => 401,
                'message' => 'id incorrecto'
            ];
        }

        $user = User::find($id);

        if ($user){
            $user->makeHidden('email_verified_at');

            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'usuario encontrado',
                'user' => $user
            ];

        } else {

            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'usuario no existe'
            ];

        }

        return response()->json($data,$data['code']);
    }

}
