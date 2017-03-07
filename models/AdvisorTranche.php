<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "advisor_tranche".
 *
 * @property integer $id
 * @property integer $from_euc
 * @property integer $commission_per10000
 * @property integer $advisor_id
 *
 * @property Advisor $advisor
 */
class AdvisorTranche extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'advisor_tranche';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['from_euc', 'commission_per10000', 'advisor_id'], 'required'],
            [['from_euc', 'commission_per10000', 'advisor_id'], 'integer'],
            [['from_euc', 'advisor_id'], 'unique', 'targetAttribute' => ['from_euc', 'advisor_id'], 'message' => 'The combination of From Euc and Advisor ID has already been taken.'],
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
            'from_euc' => Yii::t('app', 'From Euc'),
            'commission_per10000' => Yii::t('app', 'Commission Per10000'),
            'advisor_id' => Yii::t('app', 'Advisor ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvisor()
    {
        return $this->hasOne(Advisor::className(), ['id' => 'advisor_id']);
    }
}