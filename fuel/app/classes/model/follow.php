<?php  
class Model_Follow extends Orm\Model
{
	protected static $_table_name = 'follow';
	protected static $_primary_key = array('id_followed_user', 'id_follower_user');
	protected static $_properties = array(
        'id_followed_user'=> array('data_type' => 'int'), 
        'id_follower_user' => array('data_type' => 'int')
    );
}