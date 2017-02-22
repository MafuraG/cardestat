<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "configuration".
 *
 * @property string $category
 * @property string $name
 * @property string $value
 */
class Configuration extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'configuration';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category', 'name'], 'required'],
            [['category', 'name'], 'string', 'max' => 32],
            [['value'], 'string', 'max' => 256],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'category' => Yii::t('app', 'Category'),
            'name' => Yii::t('app', 'Name'),
            'value' => Yii::t('app', 'Value'),
        ];
    }
}
