<?php
namespace Fuel\Migrations;

class Songs
{
    function up()
    {
        \DBUtil::create_table('songs', array(
            'id' => array('type' => 'int', 'constraint' => 11, 'auto_increment' => true),
            'name' => array('type' => 'varchar', 'constraint' => 100),
            'artist' => array('type' => 'varchar', 'constraint' => 100),
            'urlSong' => array('type' => 'varchar', 'constraint' => 400),
            'reproductions' => array('type' => 'int', 'constraint' => 11)
        ), array('id'));

        //\DBUtil::create_index('songs', array('urlSong'), 'INDEX', 'UNIQUE');
    }

    function down()
    {
       \DBUtil::drop_table('songs');
    }
}