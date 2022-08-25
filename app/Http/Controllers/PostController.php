<?php

namespace App\Http\Controllers;

use App\Models\PostCategory;
use App\Models\Post;
use App\Models\PostTag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PostController extends Controller
{
  
    public function index(Request $request)
    {   
        if ($request->total){
            return response()->json(Post::with(['tag', 'cat','user'])->orderBy('id', 'desc')->paginate($request->total));
        } else {
            return response()->json(Post::with(['tag', 'cat','user'])->orderBy('id', 'desc')->get());
        }   
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'post' => 'required',
            'postExcerpt' => 'required',
            'metaDescription' => 'required',
            'jsonData' => 'required',
            'category_id' => 'required',
            'tag_id' => 'required',
        ]);

        $categories = $request->category_id;
        $tags = $request->tag_id;

        $postCategories = [];
        $postTags = [];
        /* Puede usar el método de transacción en la fachada de DB para ejecutar un conjunto de operaciones
            dentro de una base de datos. Si se lanza una excepción dentro del cierre de la transacción,
            la transacción se revertirá automáticamente (no se insertan datos)
            Si desea iniciar una transacción manualmente y tener un control completo
            sobre reversiones y confirmaciones, puede usar beginTransaction */
        DB::beginTransaction();
        try {
            $post = Post::create([
                'title' => $request->title,
                'slug' => $request->title,
                'post' => $request->post, 
                'postExcerpt' => $request->postExcerpt,
                'user_id' => Auth::user()->id,
                'metaDescription' => $request->metaDescription,
                'jsonData' => $request->jsonData,
                'featuredImage' => $request->featuredImage ?? null
            ]);

            // insertar etiquetas de publicación
            foreach($tags as $t){
                array_push($postTags, ['tag_id' => $t, 'post_id' => $post->id]);
            }
            PostTag::insert($postTags);    

            // insertar categorías (debe agregar created/updated_at manualmente)
            foreach($categories as $c){
                array_push($postCategories, ['category_id' => $c, 'post_id' => $post->id]);
            }

            // Use insert en lugar de crear cuando use transacción
            PostCategory::insert($postCategories); 
            
 
            DB::commit(); // Confirmar transacción
            return 'Post Created';
        } catch (\Throwable $th) {
            DB::rollback(); // En caso de errores, revierte todos los cambios
            return $th;
        }
    }


    public function show($id)
    {
        return Post::with(['tag', 'cat'])->where('id', $id)->first();
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'post' => 'required',
            'postExcerpt' => 'required',
            'metaDescription' => 'required',
            'jsonData' => 'required',
            'category_id' => 'required',
            'tag_id' => 'required',
        ]);
        $categories = $request->category_id;
        $tags = $request->tag_id;
        $postCategories = [];
        $postTags = [];
        
        $post = Post::where('id', $id)->first();
        if($post->featuredImage && $post->featuredImage != $request->featuredImage){
            $this->deleteFileFromServer($post->featuredImage); //eliminar el archivo de imagen antiguo del servidor
        }

        DB::beginTransaction();
        try {
            Post::where('id', $id)->update([
                'title' => $request->title,
                'slug' => $request->title,
                'post' => $request->post,
                'postExcerpt' => $request->postExcerpt,
                'metaDescription' => $request->metaDescription,
                'jsonData' => $request->jsonData,
                'featuredImage' => $request->featuredImage ?? null
            ]);


            // insertar categorías
            foreach ($categories as $c) {
                array_push($postCategories, ['category_id' => $c, 'post_id' => $id]);
            }
            // eliminar todas las categorías anteriores
            PostCategory::where('post_id', $id)->delete();
            PostCategory::insert($postCategories);
            
            foreach ($tags as $t) {
                array_push($postTags, ['tag_id' => $t, 'post_id' => $id]);
            }
            Posttag::where('post_id', $id)->delete();
            Posttag::insert($postTags);

            DB::commit();
            return 'Publicación actualizada';
        } catch (\Throwable $e) {
            DB::rollback();
            return $e;
        }
    }


    public function destroy(Request $request)
    {   
        $post = Post::where('id', $request->id)->first();
        if($post->featuredImage){
            $this->deleteFileFromServer($post->featuredImage); 
        }
        return $post->delete();
    }


        
    //carga de imagen del Editor.js 
    public function uploadEditorImage(Request $request){
        
        $this->validate($request, [
            'image' => 'required|mimes:jpeg,jpg,png'
        ]);
        $picName = time().'.'.$request->image->extension();
        $request->image->move(public_path('uploads'),$picName );

        // Editor js (Vue wrapper) objeto json (permite mostrar la imagen en el componente Vue)
        return response()->json([
            'success' => 1, 
            'file' => [
                'url' => env('APP_URL')."uploads/$picName"
            ]
        ]);
        

        /* QUE HACER - ELIMINAR IMÁGENES NO UTILIZADAS
        elimine las imágenes no utilizadas que no están guardadas en la publicación:
        1 hacer temp_image_table
        2 siempre que se cargue una imagen, agregue a temp_image_table id: 1, img: 34322.png
        3 antes de crear el recurso, verifique si tiene alguna imagen para ese recurso en temp_image_table,
        puedes obtener estas imágenes y eliminarlas al final
        4 subir imagen
        */
    }
}
