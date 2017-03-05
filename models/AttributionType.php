<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "attribution_type".
 *
 * @property integer $id
 * @property string $name
 * @property integer $attribution_per10000
 *
 * @property Advisor[] $advisors
 * @property Attribution[] $attributions
 */
class AttributionType extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'attribution_type';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'attribution_per10000'], 'required'],
            [['attribution_per10000'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['name', 'attribution_per10000'], 'unique', 'targetAttribute' => ['name', 'attribution_per10000'], 'message' => 'The combination of Name and Attribution Per10000 has already been taken.'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'attribution_per10000' => Yii::t('app', 'Attribution Per10000'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvisors()
    {
        return $this->hasMany(Advisor::className(), ['default_attribution_type_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributions()
    {
        return $this->hasMany(Attribution::className(), ['attribution_type_id' => 'id']);
    }
}
