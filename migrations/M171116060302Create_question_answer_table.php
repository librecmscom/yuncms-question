<?php

namespace yuncms\question\migrations;

use yii\db\Migration;

class M171116060302Create_question_answer_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%question_answer}}', [
            'id' => $this->primaryKey()->unsigned()->comment('ID'),
            'user_id' => $this->integer()->unsigned()->notNull()->comment('User Id'),
            'question_id' => $this->integer()->unsigned()->notNull()->comment('Question Id'),
            'content' => $this->text()->notNull()->comment('Content'),
            'adopted_at' => $this->integer()->unsigned()->defaultValue(0)->comment('Adopted At'),
            'supports' => $this->integer()->unsigned()->defaultValue(0)->comment('Supports'),
            'comments'=>$this->integer()->unsigned()->defaultValue(0)->comment('Comments'),
            'created_at' => $this->integer()->unsigned()->notNull()->comment('Created At'),
            'updated_at' => $this->integer()->unsigned()->notNull()->comment('Updated At'),
        ], $tableOptions);
        $this->addForeignKey('answer_fk_1', '{{%question_answer}}', 'question_id', '{{%question}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('answer_fk_2', '{{%question_answer}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%question_answer}}');
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171116060302Create_question_answer_table cannot be reverted.\n";

        return false;
    }
    */
}
