<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {   
        return view('index');
    }

    public function getPosts(Request $request)
    {   
        return response()->json(Post::with(['tag', 'cat','user'])->orderBy('id', 'desc')->paginate($request->total));
    }

    public function getCategories()
    {   
        return response()->json(Category::select('id', 'categoryName', 'iconImage')->get());
    }

    public function postSingle(Request $request, $slug){
        // obtener una publicación con datos relacionados con el usuario, la categoría y la etiqueta
        $post = Post::where('slug', $slug)->with(['cat','tag' , 'user'])->first(['id', 'title', 'post', 'user_id', 'featuredImage','created_at']);
        
        $category_ids = [];
        foreach ($post->cat as $cat) {
            array_push($category_ids, $cat->id);
        }

        // OBTENER publicaciones RELACIONADAS /('id', '!=', $post->id) - excluyendo la visitada actualmente
        /* whereHas() es similar a has() pero le permite especificar filtros adicionales para el modelo relacionado para verificar
        ('cat' y 'usuario' son relaciones de publicación, instancia de consulta $q), use() - especifica las variables utilizadas en el cierre */
        $relatedPosts = Post::with('user')->where('id', '!=', $post->id)->whereHas('cat', function($q) use($category_ids){
            $q->whereIn('category_id',$category_ids);
        })->limit(5)->orderBy('id','desc')->get((['id', 'title', 'postExcerpt', 'slug', 'user_id', 'featuredImage']));
              
        return response()->json([ 'post' => $post, 'relatedPosts' => $relatedPosts]);
    }

    public function categoryIndex(Request $request, $categoryName, $id)
    {
        $posts = Post::with(['tag', 'cat','user'])->whereHas('cat', function($q) use($id){
            $q->where('category_id',$id); // use() se necesita el método para pasar variables al cierre
        })->orderBy('id','desc')->select(['id', 'title', 'postExcerpt', 'slug', 'user_id', 'featuredImage','created_at'])->paginate($request->total);
        return response()->json([ 'posts' => $posts, 'categoryName' => $categoryName]); 
    }

    public function tagIndex(Request $request, $tagName, $id)
    {
       $posts = Post::with(['tag', 'cat','user'])->whereHas('tag', function($q) use($id){
            $q->where('tag_id',$id);
        })->orderBy('id','desc')->select(['id', 'title', 'postExcerpt', 'slug', 'user_id', 'featuredImage','created_at'])->paginate($request->total);
        return response()->json([ 'posts' => $posts, 'tagName' => $tagName]); 
    }

    public function userIndex(Request $request, $userName, $id)
    {
       $posts = Post::with(['tag', 'cat','user'])->whereHas('user', function($q) use($id){
            $q->where('user_id',$id);
        })->orderBy('id','desc')->select(['id', 'title', 'postExcerpt', 'slug', 'user_id', 'featuredImage','created_at'])->paginate($request->total);
        return response()->json([ 'posts' => $posts, 'userName' => $userName]); 
    }

    public function search(Request $request)
    {   
        $str = $request->str;
        $posts = Post::orderBy('id', 'desc')->with(['cat','tag', 'user'])->select('id', 'title', 'postExcerpt', 'slug', 'user_id', 'featuredImage','created_at'); 
  
        $posts->when($str!='', function($q) use($str){
            $q->where('title','LIKE',"%$str%")
            ->orWhereHas('cat', function($q) use($str){
                // esto buscará la tabla dinámica de relación 'cat'
                $q->where('categoryName','LIKE',"%$str%");
            })
            ->orWhereHas('tag', function($q) use($str){
                // esto buscará la tabla dinámica de relación 'tag'
                $q->where('tagName','LIKE',"%$str%");
            });
        });
 
        $posts = $posts->paginate($request->total); 
        // use anexos para obtener la URL correcta cuando use la paginación con vue (de lo contrario, se eliminará la consulta str =)
        $posts = $posts->appends($request->all()) ; 
   
        return response()->json(['posts' => $posts]);
    }
}
