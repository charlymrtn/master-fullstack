<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Register a user
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request): JsonResponse
    {
        dd($request->toArray());



        $data = [
          'status' => 'error',
          'code' => 500,
          'message' => 'El usuario no se registro correctamente'
        ];



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
    }
}
