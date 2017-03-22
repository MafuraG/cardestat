<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "advisor_tranche".
 *
 * @property integer $id
 * @property integer $from_euc
 * @property integer $commission_bp
 * @property integer $advisor_id
 *
 * @property Advisor $advisor
 */
class AdvisorTranche extends \yii\db\ActiveRecord
{
    public $from_eu;
    public $commission_pct;
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
            [['from_eu', 'commission_pct', 'advisor_id'], 'required'],
            [['from_eu', 'commission_pct', 'advisor_id'], 'number'],
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
            'from_euc' => Yii::t('app', 'Tranche From'),
            'commission_bp' => Yii::t('app', 'Commission'),
            'advisor_id' => Yii::t('app', 'Advisor'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvisor()
    {
        return $this->hasOne(Advisor::className(), ['id' => 'advisor_id']);
    }

    public function afterFind() {
        parent::afterFind();
        $this->from_eu = round($this->from_euc / 100., 2);
        $this->commission_pct = round($this->commission_bp / 100., 2);
        
    }

    public function beforeSave($insert) {
        $this->from_euc = round($this->from_eu * 100.);
        $this->commission_bp = round($this->commission_pct * 100.);
        return parent::beforeSave($insert);
    }
}
