<?php
namespace Fuel\Migrations;

class Users
{
    function up()
    {
        \DBUtil::create_table('users', array
            (
            'id' => array('type' => 'int', 'constraint' => 11, 'auto_increment' => true),
            'name' => array('type' => 'varchar', 'constraint' => 100),
            'pass' => array('type' => 'varchar', 'constraint' => 255),
            'id_device' => array('type' => 'varchar', 'constraint' => 255, 'null' => true),
            'email' => array('type' => 'varchar', 'constraint' => 200),
            'description' => array('type' => 'varchar', 'constraint' => 100, 'null' => true),
            'urlPhoto' => array('type' => 'varchar', 'constraint' => 400, 'null' => true),
            'birthday' => array('type' => 'varchar', 'constraint' => 100, 'null' => true),
            'x' => array('type' => 'float', 'constraint' => 50, 'null' => true),
            'y' => array('type' => 'float', 'constraint' => 50, 'null' => true),
            'city' => array('type' => 'varchar', 'constraint' => 100, 'null' => true),
            'id_rol' => array('type' => 'int', 'constraint' => 11)
            ), array('id'),true, 'InnoDB', 'utf8_general_ci', array(
                array(
                    'constraint' => 'foreingKeyUsersToRols',
                    'key' => 'id_rol',
                    'reference' => array(
                        'table' => 'rols',
                        'column' => 'id',
                    ),
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                )
            )
        );
        
        \DBUtil::create_index('users',array('name','email'),'INDEX','UNIQUE');
    }

    function down()
    {
       \DBUtil::drop_table('users');
    }
}