<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "correction".
 *
 * @property integer $id
 * @property integer $payroll_id
 * @property integer $corrected_euc
 * @property integer $compensated_euc
 * @property string $compensated_on
 * @property string $reason
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Payroll $payroll
 */
class Correction extends \yii\db\ActiveRecord
{
    const TRANCHES_CHANGED = 0;
    const LATE_INVOICE_PROPAGATION = 1;

    public $compensated_eu, $corrected_eu;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'correction';
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
            [['payroll_id', 'corrected_eu', 'compensated_on', 'reason'], 'required'],
            ['payroll_id', 'integer'],
            [['corrected_eu', 'compensated_eu'], 'number'],
            [['compensated_on'], 'date', 'format' => 'yyyy-MM-dd'],
            [['reason'], 'string', 'max' => 32],
            [['payroll_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payroll::className(), 'targetAttribute' => ['payroll_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'payroll_id' => Yii::t('app', 'Payroll ID'),
            'corrected_eu' => Yii::t('app', 'Correction'),
            'compensated_eu' => Yii::t('app', 'Compensation'),
            'compensated_on' => Yii::t('app', 'Compensate On'),
            'reason' => Yii::t('app', 'Reason'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPayroll()
    {
        return $this->hasOne(Payroll::className(), ['id' => 'payroll_id']);
    }
    public function afterFind() {
        parent::afterFind();
        $formatter = Yii::$app->formatter;
        $this->corrected_eu = round($this->corrected_euc / 100., 2);
        $this->compensated_eu = round($this->compensated_euc / 100., 2);
        
    }
    public function beforeSave($insert) {
        $this->corrected_euc = round($this->corrected_eu * 100.);
        $this->compensated_euc = round($this->compensated_eu * 100.);
        return parent::beforeSave($insert);
    }
    public function beforeValidate() {
        if ($this->compensated_on) $this->compensated_on = substr($this->compensated_on, 0, 7) . '-01';
        return parent::beforeValidate();
    }
}
