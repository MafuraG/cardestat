<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

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
 * @property string $payroll_month
 * @property string $comments
 * @property boolean $approved_by_direction
 * @property boolean $approved_by_accounting
 * @property string $created_at
 * @property string $updated_at
 * @property integer $sale_duration
 * @property string $property_location
 * @property string $seller_name
 * @property string $buyer_name
 * @property boolean $cardenas100
 * @property string $advisors
 * @property string $n_invoices
 * @property string $first_invoiced_at
 * @property integer $our_fee_bp
 */
class TransactionListItem extends Transaction
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
            [['first_published_at', 'last_published_at', 'option_signed_at', 'search_started_at', 'payroll_month', 'created_at', 'updated_at', 'first_invoiced_at'], 'safe'],
            [['is_new_buyer', 'is_new_seller', 'is_home_staged', 'approved_by_direction', 'approved_by_accounting', 'cardenas100'], 'boolean'],
            [['comments', 'seller_name', 'buyer_name', 'advisors'], 'string'],
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
        return ArrayHelper::merge(parent::attributeLabels(), [
            'sale_duration' => Yii::t('app', 'Sale Duration'),
            'property_location' => Yii::t('app', 'Property Location'),
            'seller_name' => Yii::t('app', 'Seller Name'),
            'buyer_name' => Yii::t('app', 'Buyer Name'),
            'cardenas100' => Yii::t('app', '100% Cárdenas'),
            'advisors' => Yii::t('app', 'Advisors'),
            'n_invoices' => Yii::t('app', 'No. Invoices'),
            'first_invoiced_at' => Yii::t('app', 'First Invoice Date'),
            'our_fee_bp' => Yii::t('app', 'Our Fee'),
        ]);
    }
}
