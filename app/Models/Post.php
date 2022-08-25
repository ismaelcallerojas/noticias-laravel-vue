<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Postcategory;
use App\Models\Posttag;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'post',
        'postExcerpt',
        'slug',
        'user_id',
        'featuredImage',
        'metaDescription',
        'views',
        'jsonData'];


        // Configure el mutador para crear un slug único cada vez que creamos una publicación
        public function setTitleAttribute($title){
            $this->attributes['title'] = $title;
            $this->attributes['slug'] = $this->uniqueSlug($title); //"titulo"
        }
    
        private function uniqueSlug($title){
            $slug = Str::slug($title, '-'); 
            $count = Post::where('slug', 'LIKE', "{$slug}%")->count(); //slug debe comenzar con title str
            $newCount = $count > 0 ? ++$count : ''; 
            return $newCount > 0 ? "$slug-$newCount" : $slug; // if already existe add count+1 to slug title
        }    

    /* Relaciones
     belongsToMany: relación de muchos a muchos. Las publicaciones pueden pertenecer a muchas categorías/etiquetas (que se comparten entre publicaciones).
     belongsTo : relación inversa de uno a uno o de muchos. La publicación pertenece a un usuario.
     */
    public function tag() 
    {
        return $this->belongsToMany(Tag::class,Posttag::class);
    }

    public function cat() 
    {
        return $this->belongsToMany(Category::class,Postcategory::class);
    }


    public function user()
    {
        return $this->belongsTo(User::class)->select('id', 'name','avatar');
    }
}
