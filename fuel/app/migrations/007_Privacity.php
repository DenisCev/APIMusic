<?php
namespace Fuel\Migrations;

class Privacity
{
    function up()
    {
        \DBUtil::create_table('privacity', array
            (
            'id' => array('type' => 'int', 'constraint' => 11, 'auto_increment' => true),
            'profile' => array('type' => 'int', 'constraint' => 11),
            'friends' => array('type' => 'int', 'constraint' => 11),
            'lists' => array('type' => 'int', 'constraint' => 11),
            'notifications' => array('type' => 'int', 'constraint' => 11),
            'location' => array('type' => 'int', 'constraint' => 11),
            'id_user' => array('type' => 'int', 'constraint' => 11)
            ), array('id'),true, 'InnoDB', 'utf8_general_ci', array(
                array(
                    'constraint' => 'foreingKeyPrivacityToUsers',
                    'key' => 'id_user',
                    'reference' => array(
                        'table' => 'users',
                        'column' => 'id',
                    ),
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                )
            )
        );
    }

    function down()
    {
       \DBUtil::drop_table('privacity');
    }
}