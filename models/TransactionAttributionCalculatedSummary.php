<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "transaction_attribution_corrected_summary".
 *
 * @property integer $transaction_id
 * @property integer $advisor_id
 * @property string $payrolled_at
 * @property string $total_attributed_sum_corrected_euc
 */
class TransactionAttributionCalculatedSummary extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transaction_attribution_calculated_summary';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transaction_id', 'advisor_id'], 'integer'],
            [['payrolled_at'], 'safe'],
            [['total_attributed_sum_calculated_euc'], 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'transaction_id' => Yii::t('app', 'Transaction ID'),
            'advisor_id' => Yii::t('app', 'Advisor ID'),
            'payrolled_at' => Yii::t('app', 'Payrolled At'),
            'total_attributed_sum_calculated_euc' => Yii::t('app', 'Total Attributed Sum Calculated Euc'),
        ];
    }
}
