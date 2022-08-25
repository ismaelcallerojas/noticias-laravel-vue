<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {   
        return response()->json(User::all());
    }


    public function store(Request $request)
    {
            // validar request 
            $this->validate($request, [
                'name' => 'required',
                'email' => 'bail|required|email|unique:users',
                'password' => 'bail|required|min:6',
                'role_id' => 'required',
            ]);
            $password = bcrypt($request->password);
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $password,
                'role_id' => $request->role_id
            ]);
            return $user;
    }


    public function update(Request $request)
    {
           // validar request 
           $this->validate($request, [
            'name' => 'required',
            'email' => "bail|required|email|unique:users,email,$request->id", //asegúrese de que el correo electrónico sea único (ignore el correo electrónico del usuario editado (obtener por id))
            'password' => 'min:6',
            'role_id' => 'required',
        ]);
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role_id' => $request->role_id,
        ];
        // comprobar si la solicitud contiene una contraseña opcional
        if($request->password){
            $password = bcrypt($request->password);
            $data['password'] = $password;
        }

        
        $user = User::where('id', $request->id)->update($data);
        return $user;
    }


    public function destroy(Request $request)
    {   
        
        Post::where('user_id', $request->id)->delete();
        
        $user = User::where('id', $request->id)->first();
        
        if ($user->avatar) {
            $this->deleteFileFromServer($user->avatar); 
        }
        return $user->delete();
    }


    public function updateAvatar(Request $request)
    {      
        $this->validate($request, [
            'avatar' => 'required',
        ]);
        
        $user = Auth::user();

        // Eliminar el archivo de avatar antiguo del servidor
        if ($user->avatar) {
            $this->deleteFileFromServer($user->avatar); 
        }
       
        $user->avatar = $request->avatar;
        
        return $user->save();
    }
}
