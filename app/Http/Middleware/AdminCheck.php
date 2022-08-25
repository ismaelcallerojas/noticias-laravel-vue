<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminCheck
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {   
        if($request->path() == '/login'){
            return $next($request); // ir al siguiente  middleware
        }

        if(!Auth::check()){
            // redireccionar al ('/login');
            return response()->json([
            'msg' => 'Accesso Denegado ... ',
            'url' => $request->path()
            ], 403);
        }

        $user = Auth::user();
        //  Utilice la relación de roles de los usuarios; si el usuario no es un administrador denegar el acceso
        if($user->role->isAdmin == 0){
            return response()->json([
                'msg' => 'Accesso Denegado ... ',
                'url' => $request->path()   
            ], 403);
        }elseif ($user->role->isAdmin == 1) {
            // si el rol del usuario es de tipo administrador  (isAdmin is 1) volver ir al siguiente  middleware
            return $next($request); 
        }
        }
}
