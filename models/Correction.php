<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "correction".
 *
 * @property integer $id
 * @property integer $transaction_payroll_id
 * @property integer $corrected_euc
 * @property integer $compensated_euc
 * @property string $compensated_at
 * @property string $reason
 * @property string $created_at
 * @property string $updated_at
 *
 * @property TransactionPayroll $transactionPayroll
 */
class Correction extends \yii\db\ActiveRecord
{
    const TRANCHES_CHANGED = 0;
    const LATE_INVOICE_PROPAGATION = 1;
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
            [['transaction_payroll_id', 'corrected_euc', 'compensated_at', 'reason'], 'required'],
            [['transaction_payroll_id', 'corrected_euc', 'compensated_euc'], 'integer'],
            [['compensated_at'], 'date', 'format' => 'yyyy-MM-dd'],
            [['reason'], 'string', 'max' => 32],
            [['transaction_payroll_id'], 'exist', 'skipOnError' => true, 'targetClass' => TransactionPayroll::className(), 'targetAttribute' => ['transaction_payroll_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'transaction_payroll_id' => Yii::t('app', 'Transaction Payroll ID'),
            'corrected_euc' => Yii::t('app', 'Corrected Euc'),
            'compensated_euc' => Yii::t('app', 'Compensated Euc'),
            'compensated_at' => Yii::t('app', 'Compensated At'),
            'reason' => Yii::t('app', 'Reason'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactionPayroll()
    {
        return $this->hasOne(TransactionPayroll::className(), ['id' => 'transaction_payroll_id']);
    }
}
