<?php  
class Model_Rols extends Orm\Model
{
	protected static $_table_name = 'rols';
	protected static $_primary_key = array('id');
	protected static $_properties = array(
        'id'=> array('data_type' => 'int'),
        'type' => array('data_type' => 'varchar')
    );

    protected static $_has_many = array(
        'user' => array(
            'key_from' => 'id',
            'model_to' => 'Model_Users',
            'key_to' => 'id_rol',
            'cascade_save' => true,
            'cascade_delete' => false,
        )
    );
}