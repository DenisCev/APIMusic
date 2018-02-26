<?php 
class Controller_Intconfig extends Controller_Base
{

    public function post_createRols()
    {
        try
        {
            $rols = Model_Rols::find('all', array(
                'where' => array(
                    array('type', 'admin')
                )
            ));

            if(!empty($rols)){

                return $this->JSONResponse(400, 'Aviso: La configuracion de roles ya ha sido realizada', '');
            }
            else
            {
                $rol = new Model_Rols();
                $rol->type = 'admin';
                $rol->save();

                $rol = new Model_Rols();
                $rol->type = 'standard';
                $rol->save();

                return $this->JSONResponse(200, 'Roles creados con exito', '');
            }  
        }
        catch (Exception $e)
        {
            return $this->JSONResponse(500, 'Error del servidor : $e', '');
        }
    }

    public function post_createAdmin()
    {
    	
            $rols = Model_Rols::find('all', array(
                'where' => array(
                    array('type', 'admin')
                )
            ));

            if(!empty($rols))
            {
                $users = Model_Users::find('all');

                $adminCount = 0;
                $maxAdmins = 1;

                foreach ($users as $key => $user)
                {
                    if($user->rol['type'] == 'admin')
                    {
                        $adminCount++;
                    }
                }

                if($adminCount >= $maxAdmins)
                {
                    return $this->JSONResponse(400, 'Aviso: La configuracion de administradores ya ha sido realizada', $adminCount);
                }

                if(!isset($_POST['name']) || !isset($_POST['pass']) || !isset($_POST['email']))
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }

                $input = $_POST;

                $ADname = $input['name'];
                $ADpass = $input['pass'];
                $ADemail = $input['email'];

                $usersName = Model_Users::find('all', array(
                    'where' => array(
                        array('name', $ADname)
                    )
                ));

                if(!empty($usersName))
                {
                    return $this->JSONResponse(400, 'Ese nombre de usuario ya esta registrado', '');
                }

                $usersEmail = Model_Users::find('all', array(
                    'where' => array(
                        array('email', $ADemail)
                    )
                ));

                if(!empty($usersEmail))
                {
                    return $this->JSONResponse(400, 'Ese email ya esta registrado', '');
                }

                $admin = new Model_Users();
                $admin->name = $ADname;
                $admin->email = $ADemail;

                $pass = $this->SecurePass($ADpass);

                $admin->pass = $pass;

                if(!empty($rols))
                {
                    foreach ($rols as $key => $rol)
                    {
                        $admin->rol = Model_Rols::find($rol->id);
                    }

                    $admin->save();

                    $privacity = new Model_Privacity();
                    $privacity->profile = 0;
                    $privacity->friends = 0;
                    $privacity->lists = 0;
                    $privacity->notifications = 0;
                    $privacity->location = 0;
                    $privacity->user = Model_Users::find($admin->id);
                    $privacity->save();

                    Model_Users::find($admin->id)->privacity = Model_Privacity::find($privacity->id)->save();

                    return $this->JSONResponse(200, 'Admin creado con exito', '');
                }
                else
                {
                    return $this->JSONResponse(400, 'Rol no encontrado', '');
                }
            }
            else
            {
                return $this->JSONResponse(400, 'Aviso: Los roles aun no an sido configurados', '');
            }
    	
    }

    public function post_createLists()
    {
        try
        {
            $authenticated = self::requestAuthenticate();

            if($authenticated == true)
            {
                $info = $this->getUserInfo();
                $user = Model_Users::find($info['id']);
            }
            else
            {
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }

            if($user->rol['type'] == 'admin')
            {
                if(!isset($_POST['name']))
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }

                $info = $this->getUserInfo();

                $input = $_POST;

                $checkName = $this->validatedName($input['name']);
       
                $listName = Model_Lists::find('all', array(
                    'where' => array(
                        array('name', $input['name']),
                        array('id_user', $info['id'])
                    ),
                ));

                if(!empty($listName))
                {
                    return $this->JSONResponse(400, 'Esa lista ya existe', '');
                }

                $list = new Model_Lists();
                $list->name = $input['name'];
                $list->editable = 0;
                $list->user = Model_Users::find($info['id']);
                $list->save();

                return $this->JSONResponse(200, 'Lista creada', '');
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

    public function post_editList()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                $info = $this->getUserInfo();
                $user = Model_Users::find($info['id']);
            }
            else
            {
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }

            if($user->rol['type'] == 'admin')
            {
                if(!isset($_POST['name']) || !isset($_POST['newName']) || !isset($_POST['id']))
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }

                $input = $_POST;

                $userLists = Model_Lists::find($input['id']);

                if(!empty($userLists))
                {
                    $nameLists = Model_Lists::find('all', array(
                        'where' => array(
                            array('id_user', $info['id']),
                            array('name', $input['newName']),
                        ),
                    ));

                    if(!empty($nameLists))
                    {
                        return $this->JSONResponse(400, 'El nombre de esa lista ya existe', '');
                    }

                    $query = DB::update('lists');
                    $query->where('name', '=', $input['name']);
                    $query->value('name', $input['newName']);
                    $query->execute();

                    return $this->JSONResponse(200, 'Nombre cambiado', '');
                }
                else
                {
                    return $this->JSONResponse(400, 'Esa lista no existe', '');
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

    public function post_deleteList()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                $info = $this->getUserInfo();
                $user = Model_Users::find($info['id']);
            }
            else
            {
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }

            if($user->rol['type'] == 'admin')
            {
                if(!isset($_POST['name']))
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }

                $info = $this->getUserInfo();
                $input = $_POST;
                
                $userList = Model_Lists::find($input['id']);

                if(!empty($userLists))
                {
                    $userList->delete();

                    return $this->JSONResponse(200, 'Lista borrada', '');
                }
                else
                {
                    return $this->JSONResponse(400, 'Esa lista no existe', '');
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
}
