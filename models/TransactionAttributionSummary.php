<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "transaction_attribution_summary".
 *
 * @property string $advisor_name
 * @property integer $transaction_id
 * @property string $payroll_month
 * @property integer $buyer_id
 * @property string $buyer_name
 * @property integer $seller_id
 * @property string $seller_name
 * @property integer $property_id
 * @property string $property_location
 * @property string $property_building_complex
 * @property string $property_reference
 * @property integer $sale_price_euc
 * @property string $n_invoices
 * @property string $invoice_issuance_dates
 * @property string $invoice_codes
 * @property integer $total_invoiced_euc
 * @property integer $our_fee_euc
 * @property integer $their_fee_euc
 * @property string $attribution_offices
 * @property string $attribution_type_names
 * @property string $attribution_type_bps
 * @property string $total_attributed_euc
 * @property integer $total_attributed_sum_euc
 */
class TransactionAttributionSummary extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transaction_attribution_summary';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transaction_id', 'buyer_id', 'seller_id', 'property_id', 'sale_price_euc', 'n_invoices', 'total_invoiced_euc', 'our_fee_euc', 'their_fee_euc', 'total_attributed_sum_euc'], 'integer'],
            [['payroll_month', 'buyer_name', 'seller_name', 'invoice_issuance_dates', 'invoice_codes', 'attribution_offices', 'attribution_type_names', 'attribution_type_bps', 'total_attributed_euc'], 'string'],
            [['advisor_name'], 'string', 'max' => 32],
            [['property_location'], 'string', 'max' => 48],
            [['property_building_complex'], 'string', 'max' => 24],
            [['property_reference'], 'string', 'max' => 12],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'advisor_name' => Yii::t('app', 'Advisor Name'),
            'transaction_id' => Yii::t('app', 'Transaction ID'),
            'payroll_month' => Yii::t('app', 'Payroll Month'),
            'buyer_id' => Yii::t('app', 'Buyer ID'),
            'buyer_name' => Yii::t('app', 'Buyer Name'),
            'seller_id' => Yii::t('app', 'Seller ID'),
            'seller_name' => Yii::t('app', 'Seller Name'),
            'property_id' => Yii::t('app', 'Property ID'),
            'property_location' => Yii::t('app', 'Property Location'),
            'property_building_complex' => Yii::t('app', 'Property Building Complex'),
            'property_reference' => Yii::t('app', 'Property Reference'),
            'sale_price_euc' => Yii::t('app', 'Sale Price Euc'),
            'n_invoices' => Yii::t('app', 'N Invoices'),
            'invoice_issuance_dates' => Yii::t('app', 'Invoice Issuance Dates'),
            'invoice_codes' => Yii::t('app', 'Invoice Codes'),
            'total_invoiced_euc' => Yii::t('app', 'Total Invoiced Euc'),
            'our_fee_euc' => Yii::t('app', 'Our Fee Euc'),
            'their_fee_euc' => Yii::t('app', 'Their Fee Euc'),
            'attribution_offices' => Yii::t('app', 'Attribution Offices'),
            'attribution_type_names' => Yii::t('app', 'Attribution Type Names'),
            'attribution_type_bps' => Yii::t('app', 'Attribution Type Bps'),
            'total_attributed_sum_euc' => Yii::t('app', 'Total Attributed Euc'),
        ];
    }
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCorrectedSummary()
    {
        return $this->hasOne(TransactionAttributionCorrectedSummary::className(), ['transaction_id' => 'transaction_id']);
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
    public function getTranches()
    {
        return $this->hasMany(AdvisorTranche::className(), ['advisor_id' => 'advisor_id']);
    }
}
