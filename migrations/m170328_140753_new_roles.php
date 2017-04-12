<?php

use yii\db\Migration;
use app\models\User;

class m170328_140753_new_roles extends Migration
{
    public function safeUp() {
        $auth = Yii::$app->authManager;
        $admin = $auth->getRole('admin');

        $this->insert('user', [
            'username' => 'adviser',
            'hash' => '$2y$13$yQ0zD/9LKz76Jlifsus8jeMXLXWtQG5AuFyF.cwzjNGFv1Ajeewiq' // password 'secret'
        ]);

        $accounting = $auth->createRole('accounting');
        $auth->add($accounting);
        $auth->addChild($admin, $accounting);
        $this->insert('user', [
            'username' => 'accountant',
            'hash' => '$2y$13$yQ0zD/9LKz76Jlifsus8jeMXLXWtQG5AuFyF.cwzjNGFv1Ajeewiq' // password 'secret'
        ]);
        $user = User::findOne(['username' => 'accountant']);
        $auth->assign($accounting, $user->id);

        $contracts = $auth->createRole('contracts');
        $auth->add($contracts);
        $auth->addChild($accounting, $contracts);
        $this->insert('user', [
            'username' => 'contractor',
            'hash' => '$2y$13$yQ0zD/9LKz76Jlifsus8jeMXLXWtQG5AuFyF.cwzjNGFv1Ajeewiq' // password 'secret'
        ]);
        $user = User::findOne(['username' => 'contractor']);
        $auth->assign($contracts, $user->id);


        return true;
    }

    public function safeDown() {
        $auth = Yii::$app->authManager;
        User::deleteAll(['or', [
            'username' => 'accountant'
        ], [
            'username' => 'contractor'
        ], [
            'username' => 'adviser'
        ]]);
        $auth->remove($auth->getRole('accounting'));
        $auth->remove($auth->getRole('contracts'));
        return true;
    }
}
