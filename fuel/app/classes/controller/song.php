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
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }

            if($user->rol['type'] == 'admin')
            {
            	if(!isset($_POST['name']) || 
                    !isset($_POST['artist']) || 
                    !isset($_POST['urlSong']) ||
                    !isset($_POST['reproductions']))
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
                }

                $input = $_POST;

                $path = 'http://' . $_SERVER['SERVER_NAME'] . '/denis/APIMusic/public/assets/music/' . $input['urlPhoto'] . '.mp3';

                $song = new Model_Pieces();
                $song->name = $input['name'];
                $song->artist = $input['artist'];
                $song->urlSong = $path;
                $song->reproductions = $input['reproductions'];

                $song->save();
                return $this->JSONResponse(200, 'Cancion creada', '');
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
                $input = $_POST;
                if(array_key_exists('id', $input))
                {
                    $song = Model_Songs::find($input['id']);
                    if(!empty($song))
                    {
                        $song->delete();
                		return $this->JSONResponse(200, 'Cancion borrada', '');
                    }
                    else
                    {
                		return $this->JSONResponse(400, 'Esa cancion no existe', '');
                    }
                }
                else
                {	
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
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
                return $this->JSONResponse(400, 'Error de autenticación', '');
            }

            if($user->rol['type'] == 'admin')
            {
            	if(!isset($_POST['id']))
                {
                    return $this->JSONResponse(400, 'Debes rellenar todos los campos', '');
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
                	return $this->JSONResponse(200, 'Operacion realizada con exito', '');
                }
                else
                {
                	return $this->JSONResponse(400, 'Esa cancion no existe', '');
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