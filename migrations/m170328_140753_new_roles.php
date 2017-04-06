<?php

use yii\db\Migration;

class m170328_140753_new_roles extends Migration
{
    public function safeUp() {
        $auth = Yii::$app->authManager;
        $admin = $auth->getRole('admin');

        $role = $auth->createRole('accounting');
        $auth->add($role);
        $auth->addChild($admin, $role);

        $role = $auth->createRole('contracts');
        $auth->add($role);
        $auth->addChild($admin, $role);

        return true;
    }

    public function safeDown() {
        $auth = Yii::$app->authManager;
        $auth->remove($auth->getRole('accounting'));
        $auth->remove($auth->getRole('contracts'));
        return true;
    }
}
