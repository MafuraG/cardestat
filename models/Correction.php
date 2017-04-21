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
 * @property integer $compensation_euc
 * @property string $compensation_on
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

    public $compensation_eu, $corrected_eu;
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
            [['payroll_id', 'corrected_eu', 'compensation_on', 'reason'], 'required'],
            ['payroll_id', 'integer'],
            ['payroll_id', 'checkPayrollMonth'],
            [['corrected_eu', 'compensation_eu'], 'number'],
            [['compensation_on'], 'date', 'format' => 'yyyy-MM-dd'],
            [['reason'], 'string', 'max' => 32],
            [['payroll_id'], 'exist', 'skipOnError' => true, 'targetClass' => Payroll::className(), 'targetAttribute' => ['payroll_id' => 'id']],
        ];
    }

    public function checkPayrollMonth($attribute, $params) 
    {
        $n = Payroll::find()->where([
            'month' => $this->compensation_on,
            'advisor_id' => $this->payroll->advisor_id
        ])->andWhere(['not', ['commission_bp' => null]])->count();
        if ($n > 0)
            $this->addError($attribute, Yii::t('app', 'The month for the correction to be compensated is already closed.'));
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
            'compensation_eu' => Yii::t('app', 'Compensation'),
            'compensation_on' => Yii::t('app', 'Compensate On'),
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
        $this->compensation_eu = round($this->compensation_euc / 100., 2);
        
    }
    public function beforeSave($insert) {
        $this->corrected_euc = round($this->corrected_eu * 100.);
        $this->compensation_euc = round($this->compensation_eu * 100.);
        return parent::beforeSave($insert);
    }
    public function beforeValidate() {
        if ($this->compensation_on) $this->compensation_on = substr($this->compensation_on, 0, 7) . '-01';
        return parent::beforeValidate();
    }
}
