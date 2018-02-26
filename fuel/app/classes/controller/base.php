<?php 
use \Firebase\JWT\JWT;

class Controller_Base extends Controller_Rest
{
    private $key = "fnap3@{ç}se2ghseo35g##53ha7s{key}opg@hseufhr378562387itgri6od/e5urj@#~62474734{[}]]|#~{#7382fgou83";

    protected function obtainData($users)
    {
        foreach ($users as $key => $user)
        {
            $userInfo = array(
	            'name' => $user->name,
	            'email' => $user->email,
                'pass' => $user->pass,
                'urlPhoto' => $user->urlPhoto,
                'description' => $user->description,
                'x' => $user->x,
                'y' => $user->y,
                'birthday' => $user->birthday,
                'city' => $user->city,
                'id_rol' => $user->id_rol,
                'id_device' => $user->id_device,
	            'id' => $user->id
        	);
        }

        return $userInfo;
    }

    protected function encodeInfo($data)
    {
    	$token = JWT::encode($data, $this->key);
    	return $token;
    }

    protected function decodeInfo($token)
    {
	    $decodedInfo = JWT::decode($token, $this->key, array('HS256')); 
	    $info_array = (array) $decodedInfo;
	    return $info_array;
    }

    protected function getUserInfo()
    {
        $headers = apache_request_headers();

        $token = $headers['Authorization'];
        $info = $this->decodeInfo($token);
        $info['token'] = $token;
        return $info;
    }

    protected function requestAuthenticate()
    {
    	try 
    	{
	        $headers = apache_request_headers();

	        if(isset($headers['Authorization']))
	        {
	            $info = $this->getUserInfo();

	            $userQuery = Model_Users::find('all', array(
	                'where' => array(
	                    array('name', $info['name']),
	                    array('pass', $info['pass'])
	                ),
	            ));
	               
	            if($userQuery != null)
                {
	                return true;
	            }
                else
                {
	                return false;
	            }
	        }
	        else
	        {
	            return false;
	        }
    	} 
    	catch (Exception $e)
    	{
    		return false;
    	}
    }
    
    protected function validatedEmail($str)
    {
        $matches = null;
        return (1 === preg_match('/^[A-z0-9\\._-]+@[A-z0-9][A-z0-9-]*(\\.[A-z0-9_-]+)*\\.([A-z]{2,6})$/', $str, $matches));
    }

    protected function validatedName($name)
    {
        $msgError = 'Nombre valido';
        $validChars = ctype_alnum($name);

        if($validChars == false){
            $msgError = "El nombre debe tener solo caracteres alfanumericos";
            return array('is' => false, 'msgError' => $msgError);
        }

        if(strlen($name) < 3){
            $msgError = "El nombre debe tener al menos 3 caracteres";
            return array('is' => false, 'msgError' => $msgError);
        }

        if(strlen($name) > 16){
            $msgError = "El nombre no puede tener más de 16 caracteres";
            return array('is' => false, 'msgError' => $msgError);
        }

        if (!preg_match('`[a-z]`',$name)){
            $msgError = "La clave debe tener al menos una letra minuscula";
            return array('is' => false, 'msgError' => $msgError);
        }

        return array('is' => true, 'msgError' => $msgError);
    }

    protected function validatedPass($pass){

        $msgError = 'Clave valida';
        $validChars = ctype_alnum($pass);

        if($validChars == false){
            $msgError = "La clave debe tener solo caracteres alfanumericos";
            return array('is' => false, 'msgError' => $msgError);
        }

        if(strlen($pass) < 6){
            $msgError = "La clave debe tener al menos 6 caracteres";
            return array('is' => false, 'msgError' => $msgError);
        }

        if(strlen($pass) > 16){
            $msgError = "La clave no puede tener más de 16 caracteres";
            return array('is' => false, 'msgError' => $msgError);
        }

        if (!preg_match('`[a-z]`',$pass)){
            $msgError = "La clave debe tener al menos una letra minuscula";
            return array('is' => false, 'msgError' => $msgError);
        }

        if (!preg_match('`[A-Z]`',$pass)){
            $msgError = "La clave debe tener al menos una letra mayuscula";
            return array('is' => false, 'msgError' => $msgError);
        }    

        if (!preg_match('`[0-9]`',$pass)){
            $msgError = "La clave debe tener al menos un caracter numerico";
            return array('is' => false, 'msgError' => $msgError);
        }

        return array('is' => true, 'msgError' => $msgError);
    }

    protected function JSONResponse($code, $message, $data)
    {
        $response = $this->response(array(
            'code' => $code,
            'message' => $message,
            'data' => $data
        ));
        return $response;
    }

    protected function SecurePass($userPass)
    {
        $timeTarget = 0.05;

        $cost = 8;
        do {
            $cost++;
            $init = microtime(true);
            $pass = password_hash($userPass, PASSWORD_BCRYPT, ["cost" => $cost]);
            $end = microtime(true);
        } while (($end - $init) < $timeTarget);

        return $pass;
    }
}