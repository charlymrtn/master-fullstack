<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{

    protected $oCategoria;

    public function __construct ()
    {
        $this->oCategoria = new Category();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $categories = $this->oCategoria::all();

        $data = [
            'status' => 'success',
            'code' => 200,
            'message' => 'todas las categorias',
            'categories' => $categories
        ];

        return response()->json($data, $data['code']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $data = [
            'status' => 'error',
            'code' => 500,
            'message' => 'La categoría no se registro correctamente'
        ];

        $validator = Validator::make($request->toArray(), [
            'name' => ['required', 'string', 'max:50', 'alpha', 'unique:categories,name'],
            'file0' => ['nullable', 'file', 'mimes:png,jpg,jpeg,gif']
        ],[
            'name.unique' => 'Ese nombre ya esta siendo utilizado'
        ]);

        if($validator->fails()) {
            $data = [
                'status' => 'error',
                'code' => 401,
                'message' => 'error en los parametros de entrada',
                'errors' => $validator->errors()
            ];

        } else {
            $inputs = $request->only('name');
            $inputs['image'] = '';
            $category = $this->oCategoria->create($inputs);
            $image = $request->file('file0');

            if ($image){
                $image_name = time().$image->getClientOriginalName();
                $image_uploaded = Storage::disk('categories')->put($image_name, File::get($image));
                if ($image_uploaded){
                    $category->image = $image_name;
                    $category->save();
                }
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La categoría se registro correctamente con imagen',
                    'category_id' =>$category->id
                ];
            }else{
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La categoría se registro correctamente',
                    'category_id' =>$category->id
                ];
            }
        }

        return response()->json($data,$data['code']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(int $category)
    {
        //
        $category = $this->oCategoria->find($category);
        if ($category){
            $data = [
                'status' => 'success',
                'code' => 200,
                'message' => 'Categoría encontrada',
                'categories' => $category
            ];
        }else{
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Categoría NO encontrada'
            ];
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, int $category)
    {
        //
        $category = $this->oCategoria->find($category);
        if ($category){
            $validator = Validator::make($request->toArray(), [
                'name' => ['required', 'string', 'max:50', 'unique:categories,name']
            ],[
                'name.unique' => 'Ese nombre ya esta siendo utilizado'
            ]);

            if($validator->fails()) {
                $data = [
                    'status' => 'error',
                    'code' => 401,
                    'message' => 'error en los parametros de entrada',
                    'errors' => $validator->errors()
                ];

            }else {
                $inputs = $request->only('name');
                $category->update($inputs);
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'La categoría se actualizo correctamente',
                    'category_id' =>$category->id
                ];
            }
        }else{
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Categoría NO encontrada'
            ];
        }

        return response()->json($data, $data['code']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $category)
    {
        //
        $category = $this->oCategoria->find($category);
        if ($category){
            try{
                $category->delete();
                $data = [
                    'status' => 'success',
                    'code' => 200,
                    'message' => 'Categoría eliminada',
                    'category_id' => $category->id
                ];
            }catch(\Exception $e){
                $data = [
                    'status' => 'error',
                    'code' => 500,
                    'message' => 'Error al eliminar'
                ];
            }
        }else{
            $data = [
                'status' => 'error',
                'code' => 404,
                'message' => 'Categoría NO encontrada'
            ];
        }

        return response()->json($data, $data['code']);
    }

    public function upload(Request $request, int $id)
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
                $category = $this->oCategoria->find($id);
                if ($category){
                    $image_name = time().$image->getClientOriginalName();
                    $image_uploaded = Storage::disk('categories')->put($image_name, File::get($image));
                    if ($image_uploaded){
                        $data = [
                            'status' => 'success',
                            'code' => 200,
                            'message' => 'Imagen cargada correctamente',
                            'image' => $image_name
                        ];
                        $category->update(['image' => $image_name]);
                        $category->save();

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
                        'code' => 404,
                        'message' => 'Categoría no encontrada'
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
}
