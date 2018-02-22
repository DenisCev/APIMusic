<?php  
class Model_News extends Orm\Model
{
	protected static $_table_name = 'news';
	protected static $_primary_key = array('id');
	protected static $_properties = array(
        'id'=> array('data_type' => 'int'), 
        'name' => array('data_type' => 'varchar'),
        'id_user' => array('data_type' => 'int'),
        'description' => array('data_type' => 'int')
    );

	protected static $_belongs_to = array(
	    'user' => array(
	        'key_from' => 'id_user',
	        'model_to' => 'Model_Users',
	        'key_to' => 'id',
	        'cascade_save' => true,
	        'cascade_delete' => false,
	    )
	);
}