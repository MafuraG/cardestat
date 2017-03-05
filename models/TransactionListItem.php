<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "transaction_list_item".
 *
 * @property integer $id
 * @property string $transaction_type
 * @property string $custom_type
 * @property string $transfer_type
 * @property string $development_type
 * @property string $first_published_at
 * @property integer $first_published_price_euc
 * @property string $last_published_at
 * @property integer $last_published_price_euc
 * @property string $option_signed_at
 * @property integer $sale_price_euc
 * @property integer $buyer_id
 * @property boolean $is_new_buyer
 * @property string $buyer_provider
 * @property integer $seller_id
 * @property boolean $is_new_seller
 * @property string $seller_provider
 * @property string $lead_type
 * @property string $search_started_at
 * @property integer $suggested_sale_price_euc
 * @property integer $passed_to_sales_by
 * @property integer $property_id
 * @property boolean $is_home_staged
 * @property integer $our_fee_euc
 * @property integer $their_fee_euc
 * @property string $payrolled_at
 * @property string $comments
 * @property boolean $approved
 * @property string $created_at
 * @property string $updated_at
 * @property integer $sale_duration
 * @property string $property_location
 * @property string $?column?
 * @property string $seller_name
 * @property string $buyer_name
 * @property boolean $cardenas100
 * @property string $advisors
 * @property string $n_invoices
 * @property string $first_invoice_issued_at
 * @property integer $our_fee_bp
 */
class TransactionListItem extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transaction_list_item';
    }

    public static function primaryKey() {
        return ['id'];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'first_published_price_euc', 'last_published_price_euc', 'sale_price_euc', 'buyer_id', 'seller_id', 'suggested_sale_price_euc', 'passed_to_sales_by', 'property_id', 'our_fee_euc', 'their_fee_euc', 'sale_duration', 'n_invoices', 'our_fee_bp'], 'integer'],
            [['first_published_at', 'last_published_at', 'option_signed_at', 'search_started_at', 'payrolled_at', 'created_at', 'updated_at', 'first_invoice_issued_at'], 'safe'],
            [['is_new_buyer', 'is_new_seller', 'is_home_staged', 'approved', 'cardenas100'], 'boolean'],
            [['comments', '?column?', 'seller_name', 'buyer_name', 'advisors'], 'string'],
            [['transaction_type', 'lead_type'], 'string', 'max' => 18],
            [['custom_type', 'transfer_type', 'development_type', 'buyer_provider', 'seller_provider'], 'string', 'max' => 32],
            [['property_location'], 'string', 'max' => 48],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'transaction_type' => Yii::t('app', 'Transaction Type'),
            'custom_type' => Yii::t('app', 'Custom Type'),
            'transfer_type' => Yii::t('app', 'Transfer Type'),
            'development_type' => Yii::t('app', 'Development Type'),
            'first_published_at' => Yii::t('app', 'First Published At'),
            'first_published_price_euc' => Yii::t('app', 'First Published Price Euc'),
            'last_published_at' => Yii::t('app', 'Last Published At'),
            'last_published_price_euc' => Yii::t('app', 'Last Published Price Euc'),
            'option_signed_at' => Yii::t('app', 'Option Signed At'),
            'sale_price_euc' => Yii::t('app', 'Sale Price Euc'),
            'buyer_id' => Yii::t('app', 'Buyer ID'),
            'is_new_buyer' => Yii::t('app', 'Is New Buyer'),
            'buyer_provider' => Yii::t('app', 'Buyer Provider'),
            'seller_id' => Yii::t('app', 'Seller ID'),
            'is_new_seller' => Yii::t('app', 'Is New Seller'),
            'seller_provider' => Yii::t('app', 'Seller Provider'),
            'lead_type' => Yii::t('app', 'Lead Type'),
            'search_started_at' => Yii::t('app', 'Search Started At'),
            'suggested_sale_price_euc' => Yii::t('app', 'Suggested Sale Price Euc'),
            'passed_to_sales_by' => Yii::t('app', 'Passed To Sales By'),
            'property_id' => Yii::t('app', 'Property ID'),
            'is_home_staged' => Yii::t('app', 'Is Home Staged'),
            'our_fee_euc' => Yii::t('app', 'Our Fee Euc'),
            'their_fee_euc' => Yii::t('app', 'Their Fee Euc'),
            'payrolled_at' => Yii::t('app', 'Payrolled At'),
            'comments' => Yii::t('app', 'Comments'),
            'approved' => Yii::t('app', 'Approved'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
            'sale_duration' => Yii::t('app', 'Sale Duration'),
            'property_location' => Yii::t('app', 'Property Location'),
            '?column?' => Yii::t('app', '?column?'),
            'seller_name' => Yii::t('app', 'Seller Name'),
            'buyer_name' => Yii::t('app', 'Buyer Name'),
            'cardenas100' => Yii::t('app', 'Cardenas100'),
            'advisors' => Yii::t('app', 'Advisors'),
            'n_invoices' => Yii::t('app', 'N Invoices'),
            'first_invoice_issued_at' => Yii::t('app', 'First Invoice Issued At'),
            'our_fee_bp' => Yii::t('app', 'Our Fee Bp'),
        ];
    }
}
