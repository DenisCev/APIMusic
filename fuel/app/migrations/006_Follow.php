<?php
namespace Fuel\Migrations;

class Follow
{
    function up()
    {
        \DBUtil::create_table('follow', array(
            'id_followed_user' => array('type' => 'int', 'constraint' => 11),
            'id_follower_user' => array('type' => 'int', 'constraint' => 11)
            ), array('id_followed_user' , 'id_follower_user'), true, 'InnoDB', 'utf8_general_ci',
            array(
                array(
                    'constraint' => 'foreingKeyFollowedToUsers',
                    'key' => 'id_followed_user',
                    'reference' => array(
                        'table' => 'users',
                        'column' => 'id',
                    ),
                    'on_update' => 'CASCADE',
                    'on_delete' => 'CASCADE'
                ),
                array(
                    'constraint' => 'foreingKeyFollowerToUsers',
                    'key' => 'id_follower_user',
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
       \DBUtil::drop_table('follow');
    }
}