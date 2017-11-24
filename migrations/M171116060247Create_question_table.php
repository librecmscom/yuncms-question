<?php

namespace yuncms\question\migrations;

use yii\db\Migration;

class M171116060247Create_question_table extends Migration
{

    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%question}}', [
            'id' => $this->primaryKey()->unsigned()->comment('Id'),
            'user_id' => $this->integer()->unsigned()->notNull()->comment('User Id'),
            'title' => $this->string(100)->notNull()->comment('Title'),
            'alias' => $this->string(100)->notNull()->comment('Alias'),
            'price' => $this->smallInteger(6)->unsigned()->defaultValue(0)->comment('Price Id'),
            'hide' => $this->boolean()->defaultValue(false)->comment('Hide'),
            'content' => $this->text()->notNull()->comment('Content'),
            'answers' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Answers'),
            'views' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Views'),
            'followers' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Followers'),
            'collections' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Collections'),
            'comments' => $this->integer()->unsigned()->notNull()->defaultValue(0)->comment('Comments'),
            'status' => $this->smallInteger()->unsigned()->notNull()->defaultValue(1)->comment('Status'),
            'created_at' => $this->integer()->unsigned()->notNull()->comment('Created At'),
            'updated_at' => $this->integer()->unsigned()->notNull()->comment('Updated At'),
        ], $tableOptions);
        $this->addForeignKey('{{%question_fk_1}}', '{{%question}}', 'user_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function safeDown()
    {
        $this->dropTable('{{%question}}');
    }


    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "M171116060247Create_question_table cannot be reverted.\n";

        return false;
    }
    */
}
