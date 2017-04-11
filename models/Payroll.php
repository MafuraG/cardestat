<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "payroll".
 *
 * @property integer $id
 * @property string $month
 * @property integer $advisor_id
 * @property integer $commission_bp
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Correction[] $corrections
 * @property Advisor $advisor
 * @property Attribution[] $attributions
 */
class Payroll extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'payroll';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['month', 'advisor_id'], 'required'],
            [['month', 'created_at', 'updated_at'], 'safe'],
            [['advisor_id', 'commission_bp'], 'integer'],
            [['advisor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Advisor::className(), 'targetAttribute' => ['advisor_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'month' => Yii::t('app', 'Month'),
            'advisor_id' => Yii::t('app', 'Advisor ID'),
            'commission_bp' => Yii::t('app', 'Commission Bp'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorrections()
    {
        return $this->hasMany(Correction::className(), ['payroll_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvisor()
    {
        return $this->hasOne(Advisor::className(), ['id' => 'advisor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributions()
    {
        return $this->hasMany(Attribution::className(), ['payroll_id' => 'id']);
    }
}
