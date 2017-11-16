<?php

namespace yuncms\question\migrations;

use yii\db\Migration;

class M171116060315Create_question_tag_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%question_tag}}', [
            'question_id' => $this->integer()->unsigned()->notNull()->comment('Question ID'),
            'tag_id' => $this->integer()->unsigned()->notNull()->comment('Tag ID'),
        ], $tableOptions);
        $this->addPrimaryKey('', '{{%question_tag}}', ['question_id', 'tag_id']);
        $this->addForeignKey('question_tag_fk_1', '{{%question_tag}}', 'question_id', '{{%question}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('question_tag_fk_2', '{{%question_tag}}', 'tag_id', '{{%tag}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%question_tag}}');
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171116060315Create_question_tag_table cannot be reverted.\n";

        return false;
    }
    */
}
