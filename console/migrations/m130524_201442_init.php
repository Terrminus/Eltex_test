<?php

use yii\db\Migration;

class m130524_201442_init extends Migration
{
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull()->unique(),

            'last_name' => $this->string()->notNull(),
            'first_name' => $this->string()->notNull(),
            'birth_date' => $this->date()->null(),
            'comment' => $this->text(),
            'last_login' => $this->date()->null(),
            'avatar' => $this->string()->null(),

            'auth_key' => $this->string(32)->notNull(),
            'password_hash' => $this->string()->notNull(),
            'password_reset_token' => $this->string()->unique(),
            'email' => $this->string()->notNull()->unique(),

            'status' => $this->smallInteger()->notNull()->defaultValue(10),

            'created_at' => $this->timestamp(),
            'updated_at' => $this->timestamp(),

        ], $tableOptions);

        $this->insert('{{%user}}', [
            'username' => 'admin',
            'last_name' => 'Adminchenko',
            'first_name' => 'Admin',
            'auth_key' => Yii::$app->security->generateRandomString(),
            'password_hash' => Yii::$app->security->generatePasswordHash('admin'),
            'email' => 'admin@yii2app.com',
            'status' => \common\models\User::STATUS_ACTIVE,
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%user}}');
    }
}
