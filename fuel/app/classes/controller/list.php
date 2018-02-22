<?php 
class Controller_List extends Controller_Base
{
    public function post_create()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
        		if(!isset($_POST['name']))
                {
                    return $this->EmptyError();
                }

                $info = $this->getUserInfo();

                $input = $_POST;

                $checkName = $this->validatedName($input['name']);

                if($checkName['is'] == true)
                {
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
                    $list->editable = 1;
                    $list->user = Model_Users::find($info['id']);
                    $list->save();

                    $response = $this->response(array(
                        'code' => 200,
                        'message' => 'lista creada',
                        'data' => ''
                    ));

                    return $response;
                }
                else
                {
                    $response = $this->response(array(
                    'code' => 400,
                    'message' => $checkName['msgError'],
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

    public function post_add()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                if(!isset($_POST['id_song']) || !isset($_POST['id_list']))
                {
                    $this->EmptyError();
                }

                $info = $this->getUserInfo();

                $input = $_POST;

                $list = Model_Lists::find($input['id_list']);

                if(empty($list))
                {
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Esa lista no existe',
                        'data' => ''
                    ));
                    return $response;
                }

                $song = Model_Songs::find($input['id_song']);

                if(empty($song))
                {
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Esa cancion no existe',
                        'data' => ''
                    ));
                    return $response;
                }

                $addName = Model_Add::find('all', array(
                    'where' => array(
                        array('id_list', $input['id_list']),
                        array('id_song', $input['id_song'])
                    ),
                ));

                if(!empty($addName))
                {
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Esa cancion ya existe en esta lista',
                        'data' => ''
                    ));
                    return $response;
                }

                $list = Model_Lists::find($input['id_list']);
                $list->song[] = Model_Songs::find($input['id_song']);
                $list->save();

                $response = $this->response(array(
                    'code' => 200,
                    'message' => 'Cancion agregada',
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

    public function post_removeFromList()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                if(!isset($_POST['id_song']) || !isset($_POST['id_list']))
                {
                    return $this->EmptyError();
                }

                $info = $this->getUserInfo();

                $input = $_POST;

                $songsFromList = Model_Add::find('all', array(
                    'where' => array(
                        array('id_list', $input['id_list']),
                        array('id_song', $input['id_song'])
                    ),
                ));

                if(!empty($songsFromList)){
                    foreach ($songsFromList as $key => $song)
                    {
                        $song->delete();
                    }

                    $response = $this->response(array(
                        'code' => 200,
                        'message' => 'Cancion eliminada de la lista',
                        'data' => ''
                    ));
                    return $response;
                }
                else
                {
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Esa cancion no existe en la lista',
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

    public function get_lists()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                $info = $this->getUserInfo();

                $userLists = Model_Lists::find('all', array(
                    'where' => array(
                        array('id_user', $info['id'])
                    )
                ));

                if(!empty($userLists))
                {
                    foreach ($userLists as $key => $list)
                    {
                        $lists[] = $list['name'];
                    }

                    $response = $this->response(array(
                        'code' => 200,
                        'message' => 'Listas obtenidas',
                        'data' => $lists
                    ));
                    return $response;
                }
                else
                {
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'No existen listas asociadas a esta cuenta',
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

    public function get_songsFromList()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                if(!isset($_GET['id_list']))
                {
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Debes rellenar todos los campos',
                        'data' => ''
                    ));
                    return $response;
                }

                $info = $this->getUserInfo();

                $input = $_GET;

                $songsFromList = Model_Add::find('all', array(
                    'where' => array(
                        array('id_list', $input['id_list'])
                    ),
                ));

                if(!empty($songsFromList)){
                    foreach ($songsFromList as $key => $songList)
                    {
                        $songsOfList[] = Model_Pieces::find($songList->id_piece);
                    }

                    foreach ($songsOfList as $key => $song)
                    {
                        $songs[] = array(
                            "name" => $song->name,
                            "side" => $song->side,
                            "element" => $song->element,
                            "rarity" => $song->rarity,
                            "life" => $song->life,
                            "damage" => $song->damage,
                            "speed" => $song->speed,
                            "cadence" => $song->cadence,
                            "description" => $song->description
                        );

                    }  

                    $response = $this->response(array(
                        'code' => 200,
                        'message' => 'Piezas encontradas',
                        'data' => $songs
                    ));
                    return $response;
                }
                else
                {
                   $response = $this->response(array(
                        'code' => 400,
                        'message' => 'No existen piezas en esa la lista',
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

    public function post_edit()
    {
        try
        {
            $authenticated = $this->requestAuthenticate();

            if($authenticated == true)
            {
                if(!isset($_POST['name']) || !isset($_POST['newName']))
                {
                    return $this->EmptyError();
                }

                $info = $this->getUserInfo();
                $input = $_POST;

                $checkName = $this->validatedName($input['newName']);

                if($checkName['is'] == true)
                {
                    $userLists = Model_Lists::find('all', array(
                        'where' => array(
                            array('id_user', $info['id']),
                            array('name', $input['name']),
                        ),
                    ));

                    if(!empty($userLists))
                    {
                        foreach ($userLists as $key => $list)
                        {
                            if($list->editable == 0){
                                $response = $this->response(array(
                                    'code' => 400,
                                    'message' => 'Esta lista no se puede editar',
                                    'data' => ''
                                ));
                                return $response;
                            }
                        }

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
                    $response = $this->response(array(
                        'code' => 400,
                        'message' => $checkName['msgError'],
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
                if(!isset($_POST['name']))
                {
                    return $this->EmptyError();
                }

                $info = $this->getUserInfo();
                $input = $_POST;
                
                $userLists = Model_Lists::find('all', array(
                    'where' => array(
                        array('id_user', $info['id']),
                        array('name', $input['name']),
                    ),
                ));

                if(!empty($userLists))
                {
                    foreach ($userLists as $key => $list)
                    {
                        if($list->editable == 0){
                            $response = $this->response(array(
                                'code' => 400,
                                'message' => 'Esta lista no se puede editar',
                                'data' => ''
                            ));
                            return $response;
                        }

                        if($list->editable == 1){
                            $list->delete();
                            $response = $this->response(array(
                                'code' => 200,
                                'message' => 'Lista borrada',
                                'data' => ''
                            ));
                            return $response;
                        }
                    } 
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