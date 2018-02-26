<?php  
class Model_Songs extends Orm\Model
{
	protected static $_table_name = 'songs';
	protected static $_primary_key = array('id');
	protected static $_properties = array(
        'id'=> array('data_type' => 'int'), 
        'name' => array('data_type' => 'varchar'),
        'artist' => array('data_type' => 'varchar'),
        'urlSong' => array('data_type' => 'varchar'),
        'reproductions' => array('data_type' => 'int')
    );
	
    protected static $_many_many = array(
	    'list' => array(
	        'key_from' => 'id',
	        'key_through_from' => 'id_list',
	        'table_through' => 'add',
	        'key_through_to' => 'id_song',
	        'model_to' => 'Model_Lists',
	        'key_to' => 'id',
	        'cascade_save' => true,
	        'cascade_delete' => false,
	    )
	);
}