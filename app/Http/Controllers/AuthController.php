<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{   

    public function index(Request $request)
    {
        // primero verifique si ha iniciado sesión y es usuario administrador ... 
        if(!Auth::check() && $request->path() != '/login'){
            return redirect('/login'); // no iniciado sesión y no en la página de inicio de sesión (redirigir para iniciar sesión)
        }
        if(!Auth::check() && $request->path() == '/login' ){
            return view('index'); // no ha iniciado sesión y ya está en la página principal (render a la página principal)
        }
        // ya ha iniciado sesión... así que verifique si es un usuario administrador...
        $user = Auth::user();
        
        if($user->role->isAdmin == 0){
            return redirect('/'); 
            // redireccionar al('/login'); 
        }
        if($request->path() == 'login'){
            return redirect('/');
        }
        
        return view('index');
    }


    public function auth ()
    {   
        if(Auth::check()){
            $user = Auth::user();
            $role = Auth::user()->role;
            return response()->json(['user'=> $user,'role' => $role]);
        }
        else{
            return response()->json(['user'=> false, 'msg'=>'El usuario no está autenticado']);
        }
    }


    public function login(Request $request){
            
        $this->validate($request, [
            'email' => 'bail|required|email',
            'password' => 'bail|required|min:6',
        ]);
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();

            // Cerrar sesión automáticamente si el rol de usuario no es de tipo administrador
            if($user->role->isAdmin == 0){
                // Auth::logout();
                return response()->json([
                    'redirect' => '/', 
                    'msg' => 'Usuario, Estás logueado', 
                    'user' => $user,
                    'role' => $user->role
                ]);
            }
            return response()->json([
                'redirect' => '/app', 
                'msg' => 'Administrador, Estás logueado', 
                'user' => $user,
                'role' => $user->role
            ]);
        }else{
            return response()->json([
                'msg' => 'Datos de inicio de sesión incorrectos', 
            ], 401);
        }
    }
    

    public function logout(){
        Auth::logout();
        return response()->json([
            'msg' => 'Cierre de sesión exitoso', 
        ], 200);
        return redirect('/');
    }

}
