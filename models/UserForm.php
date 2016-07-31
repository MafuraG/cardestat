<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class UserForm extends Model
{
    public $id;
    public $username;
    public $password;
    public $password_repeat;
    public $is_admin = false;
    private $_user = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            ['id', 'integer', 'min' => 1],
            ['id', 'required', 'on' => 'update'],
            ['id', 'exist', 'targetAttribute' => 'id', 'targetClass' => User::className(), 'on' => 'update'],
            ['username', 'unique', 'targetClass' => 'app\models\User', 'on' => 'create'],
            [['username', 'password', 'password_repeat'], 'string', 'max' => 32],
            ['password', 'string', 'min' => 6],
            ['username', 'string', 'min' => 5],
            [['username', 'password', 'password_repeat'], 'required', 'on' => 'create'],
            ['password_repeat', 'compare', 'compareAttribute' => 'password', 'message' => \Yii::t('app', 'Passwords don\'t match')],
            // rememberMe must be a boolean value
            ['is_admin', 'boolean'],
            // password is validated by validatePassword()
        ];
    }
    public function attributeLabels() {
        return [
            'password' => \Yii::t('app', 'Password'),
            'password_repeat' => \Yii::t('app', 'Repeat password'),
            'username' => \Yii::t('app', 'Username'),
            'is_admin' => \Yii::t('app', 'Is admin?'),
        ];
    }
}
