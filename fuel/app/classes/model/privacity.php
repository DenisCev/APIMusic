<?php  
class Model_Privacity extends Orm\Model
{
	protected static $_table_name = 'privacity';
	protected static $_primary_key = array('id');
	protected static $_properties = array(
        'id' => array('data_type' => 'int'),
        'profile' => array('data_type' => 'int'),
        'friends' => array('data_type' => 'int'),
        'lists' => array('data_type' => 'int'),
        'notifications' => array('data_type' => 'int'),
        'location' => array('data_type' => 'int'),
        'id_user' => array('data_type' => 'int')
    );

    protected static $_belongs_to = array(
        'user' => array(
            'key_from' => 'id_user',
            'model_to' => 'Model_Users',
            'key_to' => 'id',
            'cascade_save' => true,
            'cascade_delete' => false
        )
    );
}