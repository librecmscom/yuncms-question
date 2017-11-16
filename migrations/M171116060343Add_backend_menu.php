<?php

namespace yuncms\question\migrations;

use yii\db\Query;
use yii\db\Migration;

class M171116060343Add_backend_menu extends Migration
{

    public function safeUp()
    {
        $this->insert('{{%admin_menu}}', [
            'name' => '问答管理',
            'parent' => 8,
            'route' => '/question/question/index',
            'icon' => 'fa-question',
            'sort' => NULL,
            'data' => NULL
        ]);

        $id = (new Query())->select(['id'])->from('{{%admin_menu}}')->where(['name' => '问答管理', 'parent' => 8,])->scalar($this->getDb());
        $this->batchInsert('{{%admin_menu}}', ['name', 'parent', 'route', 'visible', 'sort'], [
            ['问题查看', $id, '/question/question/view', 0, NULL],
        ]);
    }

    public function safeDown()
    {
        $id = (new Query())->select(['id'])->from('{{%admin_menu}}')->where(['name' => '问答管理', 'parent' => 8,])->scalar($this->getDb());
        $this->delete('{{%admin_menu}}', ['parent' => $id]);
        $this->delete('{{%admin_menu}}', ['id' => $id]);
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171116060343Add_backend_menu cannot be reverted.\n";

        return false;
    }
    */
}
