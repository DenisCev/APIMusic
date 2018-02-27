<?php 
class Controller_User extends Controller_Base
{
    public function post_create()
    {
    	try
    	{
    		if(!isset($_POST['name']) || 
                !isset($_POST['pass']) || 
                !isset($_POST['email']))
            {
                return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
            }

            $input = $_POST;
          
            if(empty($input['name']) || strlen($input['name']) < 4)
            {
                return $this->JSONResponse(400, 'El nombre debe de tener almenos 4 caracteres', '');
            }

            $usersName = Model_Users::find('all', array(
                'where' => array(
                    array('name', $input['name'])
                )
            ));

            $usersEmail = Model_Users::find('all', array(
                'where' => array(
                    array('email', $input['email'])
                )
            ));

            if(!empty($usersName))
            {
                return $this->JSONResponse(400, 'Ese usuario ya esta registrado', '');
            }

            if(!empty($usersEmail))
            {
                return $this->JSONResponse(400, 'Ese email ya esta registrado', '');
            }

            $checkUserName = $this->validatedName($input['name']);

            if($checkUserName['is'] == true)
            {
                $checkEmail = $this->validatedEmail($input['email']);

                if($checkEmail == true)
                {
                    $checkPass = $this->validatedPass($input['pass']);

                    if($checkPass['is'] == true)
                    {
                        $pass = $this->SecurePass($input['pass']);

                        $user = new Model_Users();
                        $user->name = $input['name'];
                        $user->email = $input['email'];
                        $user->pass = $pass;

                        $rols = Model_Rols::find('all', array(
                            'where' => array(
                                array('type', 'standard')
                            )
                        ));

                        if(!empty($rols)){
                            foreach ($rols as $key => $rol)
                            {
                                $user->rol = Model_Rols::find($rol->id);
                            }

                            $user->save();

                            $privacity = new Model_Privacity();
                            $privacity->profile = 1;
                            $privacity->friends = 1;
                            $privacity->lists = 1;
                            $privacity->notifications = 1;
                            $privacity->location = 0;
                            $privacity->user = Model_Users::find($user->id);
                            $privacity->save();

                            Model_Users::find($user->id)->privacity = Model_Privacity::find($privacity->id)->save();

                            return $this->JSONResponse(200, 'Usuario creado con exito', '');
                        }
                        else
                        {
                            return $this->JSONResponse(400, 'Rol no encontrado', '');
                        }
                    }
                    else
                    {
                        return $this->JSONResponse(400, $checkPass['msgError'], '');
                    }
                }
                else
                {
                    return $this->JSONResponse(400, 'Formato de email no valido', '');
                }
            }
            else
            {
                return $this->JSONResponse(400, $checkUserName['msgError'], '');
            }
    	}
        catch (Exception $e)
    	{
    		return $this->JSONResponse(500, 'Error del servidor : $e', '');
    	}
    }

    public function get_login()
    {
        
        try
        {
            if(!isset($_GET['name']) || 
                !isset($_GET['pass'])) 
            {
                return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
            }

            $input = $_GET;

            $checkPass = $this->validatedPass($input['pass']);

            if($checkPass['is'] == true)
            {

                $users = Model_Users::find('all', array(
                    'where' => array(
                        array('name', $input['name'])
                    ),
                ));

                if(empty($users))
                {
                    return $this->JSONResponse(400, 'Usuario incorrecto', '');
                }

                $userData = self::obtainData($users);

                if (password_verify($input['pass'], $userData['pass'])) 
                {
                    $token = $this->encodeInfo($userData);

                    $user = Model_Users::find($userData['id']);

                    $displayInfo = array(
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'description' => $userData['description'],
                        'x' => $userData['x'],
                        'y' => $userData['y'],
                        'birthday' => $userData['birthday'],
                        'city' => $userData['city'],
                        'id_rol' => $userData['id_rol'],
                        'rol' => $user->rol['type'],
                        'urlPhoto' => $userData['urlPhoto'],
                        'id_device' => $userData['id_device'],
                        'token' => $token,
                        'id' => $userData['id']
                    );
                    
                    if($user->rol['type'] == 'admin')
                    {
                        $lists = Model_Lists::find('all', array(
                            'where' => array(
                                array('name', 'Las mas escuchadas')
                            )
                        ));

                        if(count($lists) == 0)
                        {
                            $list = new Model_Lists();
                            $list->name = 'Las mas escuchadas';
                            $list->editable = 0;
                            $list->user = Model_Users::find($userData['id']);
                            $list->save();
                            $list = null;

                            $list = new Model_Lists();
                            $list->name = 'Todas las canciones';
                            $list->editable = 0;
                            $list->user = Model_Users::find($userData['id']);
                            $list->save();
                        }

                        return $this->JSONResponse(200, 'Admin logeado', $displayInfo);
                    }

                    $listFav = Model_Lists::find('all', array(
                        'where' => array(
                            array('name', 'Favoritas'),
                            array('editable', 0),
                            array('id_user', $userData['id'])
                        ),
                    ));

                    if(empty($listFav))
                    {
                        $list = new Model_Lists();
                        $list->name = 'Favoritas';
                        $list->editable = 0;
                        $list->user = Model_Users::find($userData['id']);
                        $list->save();
                        $list = null;

                        $list = new Model_Lists();
                        $list->name = 'Por descubrir';
                        $list->editable = 0;
                        $list->user = Model_Users::find($userData['id']);
                        $list->save();
                        $list = null;

                        $list = new Model_Lists();
                        $list->name = 'Ultimas escuchadas';
                        $list->editable = 0;
                        $list->user = Model_Users::find($userData['id']);
                        $list->save();
                    }
                    
                    return $this->JSONResponse(200, 'Usuario logeado', $displayInfo);
                } 
                else 
                {
                    return $this->JSONResponse(400, 'Clave incorrecta', '');
                }
            }
            else
            {
                return $this->JSONResponse(400, $checkPass['msgError'], '');
            }
        }
        catch (Exception $e)
        {
            return $this->JSONResponse(500, 'Error del servidor : $e', '');
        }
    }

    public function get_checkToRecoverPass()
    {
        try
        {
            if(!isset($_GET['name']) || 
                !isset($_GET['email'])) 
            {
                return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
            }

            $input = $_GET;
            
            $users = Model_Users::find('all', array(
                'where' => array(
                    array('name', $input['name']),
                    array('email', $input['email'])
                ),
            ));

            if(empty($users))
            {
                return $this->JSONResponse(400, 'Usuario o email incorrectos', '');
            }

            $userData = $this->obtainData($users);

            $token = $this->encodeInfo($userData);

            return $this->JSONResponse(200, 'Usuario encontrado', '');
        }
        catch (Exception $e)
        {
            return $this->JSONResponse(500, 'Error del servidor : $e', '');
        }
    }
    
    public function post_editPass()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                if(!isset($_POST['pass'])) 
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }   

                $info = $this->getUserInfo();

                $input = $_POST;

                $checkPass = $this->validatedPass($input['pass']);

                if($checkPass['is'] == true)
                {
                    $pass = $this->SecurePass($input['pass']);
                    
                    $query = DB::update('users');
                    $query->where('id', '=', $info['id']);
                    $query->value('pass', $pass);
                    $query->execute();

                    return $this->JSONResponse(200, 'Contraseña cambiada con exito', '');
                }
                else
                {
                    return $this->JSONResponse(400, $checkPass['msgError'], '');
                }
            }
            else
            {
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }
        }
        catch (Exception $e)
        {
            return $this->JSONResponse(500, 'Error del servidor : $e', '');
        }
    }

	public function post_delete()
	{
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                if(!isset($_POST['id'])) 
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                } 

                $info = $this->getUserInfo();

                $input = $_POST;

                if($info['id'] == $input['id'])
                {
                    $user = Model_Users::find($info['id']);
                    $user->delete();

                    return $this->JSONResponse(200, 'Usuario borrado', '');
                }
                else
                {
                    return $this->JSONResponse(400, 'No puedes borrar a otros usuarios', '');
                } 
            }
            else
            {
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }
        }
        catch (Exception $e)
        {
            return $this->JSONResponse(500, 'Error del servidor : $e', '');
        }    
	}

    public function post_editPhoto()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                if(!isset($_POST['urlPhoto'])) 
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }   

                $info = $this->getUserInfo();

                $input = $_POST;

                $path = 'http://' . $_SERVER['SERVER_NAME'] . '/denis/APIMusic/public/assets/img/' . $input['urlPhoto'] . '.png';

                $query = DB::update('users');
                $query->where('id', '=', $info['id']);
                $query->value('urlPhoto', $path);
                $query->execute();

                return $this->JSONResponse(200, 'Foto cambiada con exito', $path);
            }
            else
            {
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }
        }
        catch (Exception $e)
        {
            return $this->JSONResponse(500, 'Error del servidor : $e', '');
        }
    }
}
