<?php

namespace yuncms\question\migrations;

use yii\db\Migration;

class M171116060328Add_column_of_user_extra extends Migration
{

    public function safeUp()
    {
        $this->addColumn('{{%user_extra}}', 'questions', $this->integer()->unsigned()->defaultValue(0)->comment('Questions'));
        $this->addColumn('{{%user_extra}}', 'answers', $this->integer()->unsigned()->defaultValue(0)->comment('Answers'));
        $this->addColumn('{{%user_extra}}', 'adoptions', $this->integer()->unsigned()->defaultValue(0)->comment('Adoptions'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user_extra}}', 'questions');
        $this->dropColumn('{{%user_extra}}', 'answers');
        $this->dropColumn('{{%user_extra}}', 'adoptions');
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171116060328Add_column_of_user_extra cannot be reverted.\n";

        return false;
    }
    */
}
