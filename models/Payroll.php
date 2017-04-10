<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "payroll".
 *
 * @property integer $id
 * @property integer $commission_bp
 * @property integer $accumulated_euc
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Attribution[] $attributions
 * @property Correction[] $corrections
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

    public function behaviors()
    {
        return [[
            'class' => TimestampBehavior::className(),
            'value' => new Expression('now()')
        ]];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['commission_bp', 'accumulated_euc'], 'required'],
            [['commission_bp', 'accumulated_euc'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'commission_bp' => Yii::t('app', 'Commission Bp'),
            'accumulated_euc' => Yii::t('app', 'Accumulated Euc'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributions()
    {
        return $this->hasMany(Attribution::className(), ['transaction_id' => 'id'])
            ->via('transaction', function($q) {
                $q->where(['id' => $this->transaction_id]);
            })->andOnCondition(['advisor_id' => $this->advisor_id]);
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
    public function getTransaction()
    {
        return $this->hasOne(Transaction::className(), ['id' => 'transaction_id']);
    }

}
