<?php 
class Controller_Config extends Controller_Base
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
                $response = $this->response(array(
                    'code' => 400,
                    'message' => 'Aviso: La configuracion ya ha sido realizada',
                    'data' => ''
                ));
                return $response;
            }
            else
            {
                $rol = new Model_Rols();
                $rol->type = 'admin';
                $rol->save();

                $rol = new Model_Rols();
                $rol->type = 'standard';
                $rol->save();

                $response = $this->response(array(
                    'code' => 200,
                    'message' => 'Roles creados con exito',
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

    public function post_createAdmin()
    {
    	try
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

                if($adminCount >= $maxAdmins){
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Aviso: La configuracion ya ha sido realizada',
                        'data' => count($users)
                    ));
                    return $response;
                }

                if(!isset($_POST['name']) || !isset($_POST['pass']) || !isset($_POST['email']))
                {
                    $this->EmptyError();
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
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Ese nombre de usuario ya esta registrado',
                        'data' => ''
                    ));
                    return $response;
                }

                $usersEmail = Model_Users::find('all', array(
                    'where' => array(
                        array('email', $ADemail)
                    )
                ));

                if(!empty($usersEmail))
                {
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Ese email ya esta registrado',
                        'data' => ''
                    ));
                    return $response;
                }

                $admin = new Model_Users();
                $admin->name = $ADname;
                $admin->email = $ADemail;

                $pass = $this->SecurePass($ADpass);

                $admin->pass = $pass;

                if(!empty($rols)){
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

                    $lists = Model_Users::find('all', array(
                        'where' => array(
                            array('name', 'Las mas escuchadas')
                        )
                    ));

                    if(empty($lists))
                    {
                        $list = new Model_Lists();
                        $list->name = 'Las mas escuchadas';
                        $list->editable = 0;
                        $list->user = Model_Users::find($admin->id);
                        $list->save();
                    }

                    $response = $this->response(array(
                        'code' => 200,
                        'message' => 'Admin creado con exito',
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
                    'message' => 'Aviso: La api aun no ha sido configurada (Rols)',
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
                return $this->AuthError();
            }

            if($user->rol['type'] == 'admin')
            {
                if(!isset($_POST['name']))
                {
                    return $this->EmptyError();
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
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Esa lista ya existe',
                        'data' => ''
                    ));
                    return $response;
                }

                $list = new Model_Lists();
                $list->name = $input['name'];
                $list->editable = 0;
                $list->user = Model_Users::find($info['id']);
                $list->save();

                $response = $this->response(array(
                    'code' => 200,
                    'message' => 'Lista creada',
                    'data' => ''
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
                return $this->AuthError();
            }

            if($user->rol['type'] == 'admin')
            {
                if(!isset($_POST['name']) || !isset($_POST['newName']) || !isset($_POST['id']))
                {
                    return $this->EmptyError();
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
                        $response = $this->response(array(
                            'code' => 400,
                            'message' => 'El nombre de esa lista ya existe',
                            'data' => ''
                        ));
                        return $response;
                    }

                    $query = DB::update('lists');
                    $query->where('name', '=', $input['name']);
                    $query->value('name', $input['newName']);
                    $query->execute();

                    $response = $this->response(array(
                        'code' => 200,
                        'message' => 'Nombre cambiado',
                        'data' => ''
                    ));
                }
                else
                {
                    $response = $this->response(array(
                    'code' => 400,
                    'message' => 'Esa lista no existe',
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
                return $this->AuthError();
            }

            if($user->rol['type'] == 'admin')
            {
                if(!isset($_POST['name']))
                {
                    return $this->EmptyError();
                }

                $info = $this->getUserInfo();
                $input = $_POST;
                
                $userList = Model_Lists::find($input['id']);

                if(!empty($userLists))
                {
                    $userList->delete();

                    $response = $this->response(array(
                        'code' => 200,
                        'message' => 'Lista borrada',
                        'data' => ''
                    ));
                    return $response;
                }
                else
                {
                    $response = $this->response(array(
                    'code' => 400,
                    'message' => 'Esa lista no existe',
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
}
