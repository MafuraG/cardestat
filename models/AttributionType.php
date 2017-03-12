<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "attribution_type".
 *
 * @property integer $id
 * @property string $name
 * @property integer $attribution_bp
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
            [['name', 'attribution_bp'], 'required'],
            [['attribution_bp'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['name', 'attribution_bp'], 'unique', 'targetAttribute' => ['name', 'attribution_bp'], 'message' => 'The combination of Name and Attribution bips has already been taken.'],
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
            'attribution_bp' => Yii::t('app', 'Attribution Rate'),
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

    public static function listAll()
    {
        return ArrayHelper::map(static::find()->orderBy('name')->all(), 'id', function($el) {
            $attrPct = $el->attribution_bp / 100;
            return "$el->name $attrPct%";
        });
    }
}
