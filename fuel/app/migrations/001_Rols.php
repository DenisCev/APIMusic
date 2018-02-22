<?php
namespace Fuel\Migrations;

class Rols
{
    function up()
    {
        \DBUtil::create_table('rols', array(
        'id' => array('type' => 'int', 'constraint' => 11, 'auto_increment' => true),
        'type' => array('type' => 'varchar', 'constraint' => 100)
        ), array('id'));
    }

    function down()
    {
       \DBUtil::drop_table('rols');
    }
}