<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Helpers\JwtAuth;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Role;
use League\CommonMark\Inline\Element\Code;

class UserController extends Controller
{
    //Registro por API
    public function register(Request $request){
        //Recoger post que llega
        $json = $request->input('json', null);
        //Decodificacion del json a un objeto para usarlo en php
        $params = json_decode($json);

        //variables
        $name = (!is_null($json) && isset($params->name))? $params->name : null;
        $email = (!is_null($json) && isset($params->email))? $params->email : null;
       
        $password = (!is_null($json) && isset($params->password))? $params->password : null;
        $rol = (!is_null($json) && isset($params->rol))? $params->rol : null;
        if(!is_null($email) && !is_null($password) && !is_null($name)){
            //crea el usario
            $user = new  User();
            $user->name =$name;
            $user->email =$email;
            // $user->role = $rol;
            //cifrar el passwerd
            $pwd = hash('sha256', $password);
            $user->password =$pwd;
            $user->rol =$rol;
            //Comprobar usuario duplicado 
            $isset_user = User::where('email', $email)->first(); //Primer registro
           
                

            $data = array(
                'status' => 'success',
                'code' => 200,
                'message' => 'Usuario registrado correctamente'
            );
            if(!($isset_user)){
                //guardar el usuario
                $user->save();
                 //asignacion de roles
            switch ($user['rol']) {
                case 'admin':
                $user
                    ->roles()
                    ->attach(Role::where('name', 'admin')->first());
                break;

                case 'empleado':
                $user
                    ->roles()
                    ->attach(Role::where('name', 'empleado')->first());
                break;

                case 'cliente':
                    $user
                    ->roles()
                    ->attach(Role::where('name', 'cliente')->first());
                break;
                }
                
            }else{
                //No guardar porque ya existe
                $data = array(
                    'status' => 'error',
                    'code' => 400,
                    'message' => 'Usuario duplicado, no puede registrarse'
                );
            }
        }else{
            $data = array(
                'status' => 'Error',
                'code' => 400,
                'message' => 'Usuario no encontrado'
            );
        }
        return response()->json($data,$data['code']);
    }

    //Login por API
    public function login(Request $request){
        //Instancia del Objeto
        $jwtAuth = new JwtAuth();

        //Recibir el POST
        $json = $request->input('json', null);
        //decodificar el json y convertir en un objeto para manejarlo en php
        $param = json_decode($json);

        //comprobaciones
        //El json no sea null y exista la propiedad email dentro de param si es true se asigna el valor en caso de flase es null
        $email = (!is_null($json) && isset($param->email)) ? $param->email: null; 
        $password = (!is_null($json) && isset($param->password)) ? $param->password: null;
        $getToken = (!is_null($json) && isset($param->getToken)) ? $param->getToken: null;
        
        //cifrar la password
        $pwd = hash('sha256', $password); //'sha256 algoritmo de cifrado
        
        //Comprobacion
        if(!is_null($email) && !is_null($password) && ($getToken == null || $getToken == 'true')){
            $signup = $jwtAuth->signup($email, $pwd);
            // return response()->json($signup, 200);
        }elseif ($getToken != null) {
            //  var_dump($getToken); die();
            $signup = $jwtAuth->signup($email, $pwd, $getToken);
            // $signup = array(
            //     'status' => 'success',
            //     'code' => 200,
            //     'message' => 'Usuario registrado correctamente'
            // );
            // return response()->json("error", 400);
            //   echo "ver productos"; die();
        }else{
            $signup = array(
                'status' => 'error',
                 'code' => 400,
                'message' => 'Eviar tus datos '
            );
        }
        return response()->json($signup,200);
    }

    
}
