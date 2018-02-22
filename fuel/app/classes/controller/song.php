<?php 
class Controller_Song extends Controller_Base
{
	private $DEV_NAME = "";

    public function post_create()
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
            	if(!isset($_POST['name']) || 
                    !isset($_POST['artist']) || 
                    !isset($_POST['urlSong']) ||
                    !isset($_POST['reproductions']))
                {
                    return $this->EmptyError();
                }

                $input = $_POST;

                $path = 'http://' . $_SERVER['SERVER_NAME'] . '/denis/APIMusic/public/assets/music/' . $input['urlPhoto'] . '.mp3';

                $song = new Model_Pieces();
                $song->name = $input['name'];
                $song->artist = $input['artist'];
                $song->urlSong = $path;
                $song->reproductions = $input['reproductions'];

                $song->save();

                $response = $this->response(array(
                    'code' => 200,
                    'message' => 'Cancion creada',
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

	public function post_delete()
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
                $input = $_POST;
                if(array_key_exists('id', $input))
                {
                    $song = Model_Songs::find($input['id']);
                    if(!empty($song))
                    {
                        $song->delete();
                        $response = $this->response(array(
                            'code' => 200,
                            'message' => 'Cancion borrada',
                            'data' => ''
                        ));
                        return $response;
                    }
                    else
                    {
                        $response = $this->response(array(
                        'code' => 400,
                        'message' => 'Esa cancion no existe',
                        'data' => ''
                        ));
                        return $response;
                    }
                }
                else
                {	
                    return $this->EmptyError();
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
                $info = $this->getUserInfo();
                $user = Model_Users::find($info['id']);
            }
            else
            {
                return $this->AuthError();
            }

            if($user->rol['type'] == 'admin')
            {
            	if(!isset($_POST['id']))
                {
                    return $this->EmptyError();
                }

                $input = $_POST;

                $song = Model_Songs::find($info['id']);

                if(!empty($song))
                {

	                if(array_key_exists('name', $input))
	                {
	                	$query = DB::update('songs');
	                    $query->where('id', '=', $input['id']);
	                    $query->value('name', $input['name']);
	                    $query->execute();
	                    $query = null;
	                }

	                if(array_key_exists('artist', $input))
	                {
	                	$query = DB::update('songs');
	                    $query->where('id', '=', $input['id']);
	                    $query->value('artist', $input['artist']);
	                    $query->execute();
	                    $query = null;
	                }

	                if(array_key_exists('urlSong', $input))
	                {
	                	$path = 'http://' . $_SERVER['SERVER_NAME'] . '/denis/APIMusic/public/assets/music/' . $input['urlPhoto'] . '.mp3';

	                	$query = DB::update('songs');
	                    $query->where('id', '=', $input['id']);
	                    $query->value('urlSong', $path);
	                    $query->execute();
	                    $query = null;
	                }

                    $response = $this->response(array(
                        'code' => 200,
                        'message' => 'Operacion realizada con exito',
                        'data' => ''
                    ));
                }
                else
                {
                    $response = $this->response(array(
                    'code' => 400,
                    'message' => 'Esa cancion no existe',
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