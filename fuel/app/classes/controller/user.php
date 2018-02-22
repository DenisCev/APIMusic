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
                return $this->EmptyError();
            }

            $input = $_POST;
          
            if(empty($input['name']) || strlen($input['name']) < 4)
            {
                $response = $this->response(array(
                    'code' => 400,
                    'message' => 'El nombre debe de tener almenos 4 caracteres',
                    'data' => ''
                ));
                return $response;
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
                $response = $this->response(array(
                    'code' => 400,
                    'message' => 'Ese usuario ya esta registrado',
                    'data' => ''
                ));
                return $response;
            }

            if(!empty($usersEmail))
            {
                $response = $this->response(array(
                    'code' => 400,
                    'message' => 'Ese email ya esta registrado',
                    'data' => ''
                ));
                return $response;
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

                            $list = new Model_Lists();
                            $list->name = 'Por descubrir';
                            $list->editable = 0;
                            $list->user = Model_Users::find($user->id);
                            $list->save();

                            $list = new Model_Lists();
                            $list->name = 'Ultimas escuchadas';
                            $list->editable = 0;
                            $list->user = Model_Users::find($user->id);
                            $list->save();

                            $response = $this->response(array(
                                'code' => 200,
                                'message' => 'Usuario creado con exito',
                                'data' => ''
                            ));
                            return $response;
                        }
                        else
                        {
                            $response = $this->response(array(
                                'code' => 400,
                                'message' => 'Rol no encontrado',
                                'data' => ''
                            ));
                            return $response;
                        }
                    }
                    else
                    {
                        $response = $this->response(array(
                            'code' => 400,
                            'message' => $checkPass['msgError'],
                            'data' => ''
                        ));
                        return $response;
                    }
                }
                else
                {
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Formato de email no valido',
                        'data' => ''
                    ));
                    return $response;
                }
            }
            else
            {
                $response = $this->response(array(
                    'code' => 400,
                    'message' => $checkUserName['msgError'],
                    'data' => ''
                ));
                return $response;
            }
    	}
        catch (Exception $e)
    	{
    		return $this->ServerError();
    	}
    }

    public function get_login()
    {
        try
        {
            if(!isset($_GET['name']) || 
                !isset($_GET['pass'])) 
            {
                return $this->EmptyError();
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
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Usuario incorrecto',
                        'data' => ''
                    ));
                    return $response;
                }

                $userData = self::obtainData($users);

                if (password_verify($input['pass'], $userData['pass'])) 
                {
                    $token = $this->encodeInfo($userData);

                    $displayInfo = array(
                        'name' => $userData['name'],
                        'email' => $userData['email'],
                        'description' => $userData['description'],
                        'x' => $userData['x'],
                        'y' => $userData['y'],
                        'birthday' => $userData['birthday'],
                        'city' => $userData['city'],
                        'id_rol' => $userData['id_rol'],
                        'urlPhoto' => $userData['urlPhoto'],
                        'id_device' => $userData['id_device'],
                        'token' => $token,
                        'id' => $userData['id']
                    );

                    $listName = Model_Lists::find('all', array(
                        'where' => array(
                            array('name', 'Favorites'),
                            array('editable', 0),
                            array('id_user', $userData['id'])
                        ),
                    ));

                    if(empty($listName))
                    {
                        $list = new Model_Lists();
                        $list->name = 'Favorites';
                        $list->editable = 0;
                        $list->user = Model_Users::find($userData['id']);
                        $list->save();
                    }

                    $response = $this->response(array(
                        'code' => 200,
                        'message' => 'Usuario logeado',
                        'data' => $displayInfo
                    ));
                    return $response;
                } 
                else 
                {
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Clave incorrecta',
                        'data' => ''
                    ));
                    return $response;
                }
            }
            else
            {
                $response = $this->response(array(
                    'code' => 400,
                    'message' => $checkPass['msgError'],
                    'data' => ''
                ));
                return $response;
            }
        }
        catch (Exception $e)
        {
            return $this->ServerError();
        }
    }

    public function get_checkToRecoverPass()
    {
        try
        {
            if(!isset($_GET['name']) || 
                !isset($_GET['email'])) 
            {
                return $this->EmptyError();
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
                $response = $this->response(array(
                    'code' => 400,
                    'message' => 'Usuario o email incorrectos',
                    'data' => ''
                ));
                return $response;
            }

            $userData = $this->obtainData($users);

            $token = $this->encodeInfo($userData);

            $response = $this->response(array(
                'code' => 200,
                'message' => 'Usuario encontrado',
                'data' => $token
            ));
            return $response;
        }
        catch (Exception $e)
        {
            return $this->ServerError();
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
                    return $this->EmptyError();
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

                    $response = $this->response(array(
                        'code' => 200,
                        'message' => 'Contraseña cambiada con exito',
                        'data' => ''
                    ));
                }
                else
                {
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => $checkPass['msgError'],
                        'data' => ''
                    ));
                    return $response;
                }
            }
            else
            {
                return $this->AuthError();
            }
        }
        catch (Exception $e)
        {
            return $this->ServerError();
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
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Debes rellenar todos los campos',
                        'data' => ''
                    ));
                    return $response;
                } 

                $info = $this->getUserInfo();

                $input = $_POST;

                if($info['id'] == $input['id'])
                {
                    $user = Model_Users::find($info['id']);
                    $user->delete();

                    $response = $this->response(array(
                        'code' => 200,
                        'message' => 'usuario borrado',
                        'data' => ''
                    ));
                    return $response;
                }
                else
                {
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'No puedes borrar a otros usuarios',
                        'data' => ''
                    ));
                    return $response;
                } 
            }
            else
            {
                return $this->AuthError();
            }
        }
        catch (Exception $e)
        {
            return $this->ServerError();
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
                    return $this->EmptyError();
                }   

                $info = $this->getUserInfo();

                $input = $_POST;

                $path = 'http://' . $_SERVER['SERVER_NAME'] . '/denis/APIMusic/public/assets/img/' . $input['urlPhoto'] . '.png';

                $query = DB::update('users');
                $query->where('id', '=', $info['id']);
                $query->value('urlPhoto', $path);
                $query->execute();

                $response = $this->response(array(
                    'code' => 200,
                    'message' => 'Foto cambiada con exito',
                    'data' => $path
                ));
                return $response;
            }
            else
            {
                return $this->AuthError();
            }
        }
        catch (Exception $e)
        {
            return $this->ServerError();
        }
    }
}
