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
 * @property boolean $active
 *
 * @property Advisor[] $advisors
 * @property Attribution[] $attributions
 */
class AttributionType extends \yii\db\ActiveRecord
{
    public $attribution_pct;
    const SCENARIO_UPDATE = 'update';
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
            [['name', 'attribution_pct'], 'required'],
            [['active'], 'boolean'],
            [['attribution_pct'], 'number'],
            [['name'], 'string', 'max' => 32],
            [['name', 'attribution_pct'], 'unique', 'targetAttribute' => ['name', 'attribution_bp'], 'message' => 'The combination of Name and Attribution bips has already been taken.'],
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE] = ['active', 'name'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'active' => Yii::t('app', 'Active'),
            'attribution_pct' => Yii::t('app', 'Attribution Rate'),
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

    public static function listActive()
    {
        return ArrayHelper::map(static::find()
            ->where(['active' => true])->orderBy('name')->all(), 'id', function($el) {
            $attrPct = $el->attribution_bp / 100;
            return "$el->name $attrPct%";
        });
    }
    public static function listAll()
    {
        return ArrayHelper::map(static::find()->orderBy('name')->all(), 'id', function($el) {
            $attrPct = $el->attribution_bp / 100;
            return "$el->name $attrPct%";
        });
    }
    public function afterFind() {
        parent::afterFind();
        $this->attribution_pct = round($this->attribution_bp / 100., 2);
    }
    public function beforeSave($insert) {
        $this->attribution_bp = round($this->attribution_pct * 100.);
        return parent::beforeSave($insert);
    }
}
