<?php
namespace Fuel\Migrations;

class Lists
{
    function up()
    {
        \DBUtil::create_table('lists', array
            (
            'id' => array('type' => 'int', 'constraint' => 11, 'auto_increment' => true),
            'name' => array('type' => 'varchar', 'constraint' => 100),
            'editable' => array('type' => 'int', 'constraint' => 11),
            'id_user' => array('type' => 'int', 'constraint' => 11)
            ), array('id'),true, 'InnoDB', 'utf8_general_ci', array(
                array(
                    'constraint' => 'foreingKeyListsToUsers',
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
       \DBUtil::drop_table('lists');
    }
}