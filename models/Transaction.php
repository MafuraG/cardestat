<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "transaction".
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
 *
 * @property Attribution[] $attributions
 * @property Advisor $passedToSalesBy
 * @property Contact $buyer
 * @property Contact $seller
 * @property CustomType $customType
 * @property DevelopmentType $developmentType
 * @property LeadType $leadType
 * @property Partner $buyerProvider
 * @property Partner $sellerProvider
 * @property Property $property
 * @property TransactionType $transactionType
 * @property TransferType $transferType
 */
class Transaction extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transaction';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['first_published_at', 'last_published_at', 'option_signed_at', 'search_started_at', 'payrolled_at', 'created_at', 'updated_at'], 'safe'],
            [['first_published_price_euc', 'last_published_price_euc', 'sale_price_euc', 'buyer_id', 'seller_id', 'suggested_sale_price_euc', 'passed_to_sales_by', 'property_id', 'our_fee_euc', 'their_fee_euc'], 'integer'],
            [['option_signed_at', 'buyer_id', 'seller_id', 'property_id', 'created_at', 'updated_at'], 'required'],
            [['is_new_buyer', 'is_new_seller', 'is_home_staged', 'approved'], 'boolean'],
            [['comments'], 'string'],
            [['transaction_type', 'lead_type'], 'string', 'max' => 18],
            [['custom_type', 'transfer_type', 'development_type', 'buyer_provider', 'seller_provider'], 'string', 'max' => 32],
            [['passed_to_sales_by'], 'exist', 'skipOnError' => true, 'targetClass' => Advisor::className(), 'targetAttribute' => ['passed_to_sales_by' => 'id']],
            [['buyer_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contact::className(), 'targetAttribute' => ['buyer_id' => 'id']],
            [['seller_id'], 'exist', 'skipOnError' => true, 'targetClass' => Contact::className(), 'targetAttribute' => ['seller_id' => 'id']],
            [['custom_type'], 'exist', 'skipOnError' => true, 'targetClass' => CustomType::className(), 'targetAttribute' => ['custom_type' => 'name']],
            [['development_type'], 'exist', 'skipOnError' => true, 'targetClass' => DevelopmentType::className(), 'targetAttribute' => ['development_type' => 'name']],
            [['lead_type'], 'exist', 'skipOnError' => true, 'targetClass' => LeadType::className(), 'targetAttribute' => ['lead_type' => 'name']],
            [['buyer_provider'], 'exist', 'skipOnError' => true, 'targetClass' => Partner::className(), 'targetAttribute' => ['buyer_provider' => 'name']],
            [['seller_provider'], 'exist', 'skipOnError' => true, 'targetClass' => Partner::className(), 'targetAttribute' => ['seller_provider' => 'name']],
            [['property_id'], 'exist', 'skipOnError' => true, 'targetClass' => Property::className(), 'targetAttribute' => ['property_id' => 'id']],
            [['transaction_type'], 'exist', 'skipOnError' => true, 'targetClass' => TransactionType::className(), 'targetAttribute' => ['transaction_type' => 'name']],
            [['transfer_type'], 'exist', 'skipOnError' => true, 'targetClass' => TransferType::className(), 'targetAttribute' => ['transfer_type' => 'name']],
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
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributions()
    {
        return $this->hasMany(Attribution::className(), ['transaction_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPassedToSalesBy()
    {
        return $this->hasOne(Advisor::className(), ['id' => 'passed_to_sales_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuyer()
    {
        return $this->hasOne(Contact::className(), ['id' => 'buyer_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSeller()
    {
        return $this->hasOne(Contact::className(), ['id' => 'seller_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCustomType()
    {
        return $this->hasOne(CustomType::className(), ['name' => 'custom_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDevelopmentType()
    {
        return $this->hasOne(DevelopmentType::className(), ['name' => 'development_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLeadType()
    {
        return $this->hasOne(LeadType::className(), ['name' => 'lead_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuyerProvider()
    {
        return $this->hasOne(Partner::className(), ['name' => 'buyer_provider']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSellerProvider()
    {
        return $this->hasOne(Partner::className(), ['name' => 'seller_provider']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProperty()
    {
        return $this->hasOne(Property::className(), ['id' => 'property_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactionType()
    {
        return $this->hasOne(TransactionType::className(), ['name' => 'transaction_type']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransferType()
    {
        return $this->hasOne(TransferType::className(), ['name' => 'transfer_type']);
    }
}
