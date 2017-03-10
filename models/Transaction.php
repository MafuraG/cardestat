<?php

namespace app\models;

use Yii;
use app\models\Contact;
use app\models\Advisor;
use app\models\CustomType;
use app\models\DevelopmentType;
use app\models\LeadType;
use app\models\Invoice;
use app\models\Partner;
use app\models\Property;
use app\models\TransactionType;

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
    public $first_published_price_eu;
    public $last_published_price_eu;
    public $sale_price_eu;
    public $suggested_sale_price_eu;
    public $our_fee_eu;
    public $their_fee_eu;
    
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
            [['first_published_at', 'last_published_at', 'option_signed_at', 'search_started_at', 'payrolled_at'], 'safe'],
            [['first_published_price_eu', 'last_published_price_eu', 'sale_price_eu', 'suggested_sale_price_eu', 'our_fee_eu', 'their_fee_eu'], 'number'],
            [['buyer_id', 'seller_id', 'passed_to_sales_by', 'property_id'], 'integer'],
            [['transaction_type', 'option_signed_at', 'buyer_id', 'seller_id', 'property_id', 'created_at', 'updated_at'], 'required'],
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
            [['lead_type', 'custom_type', 'transfer_type', 'transaction_type', 'development_type', 'seller_provider', 'buyer_provider'], 'default', 'value' => null],
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
            'first_published_at' => Yii::t('app', 'First Published Date'),
            'first_published_price_eu' => Yii::t('app', 'First Published Price'),
            'last_published_at' => Yii::t('app', 'Last Published Date'),
            'last_published_price_eu' => Yii::t('app', 'Last Published Price'),
            'option_signed_at' => Yii::t('app', 'Option Signed Date'),
            'sale_price_eu' => Yii::t('app', 'Sale Price'),
            'buyer_id' => Yii::t('app', 'Buyer'),
            'is_new_buyer' => Yii::t('app', 'The Buyer Is A New Client'),
            'buyer_provider' => Yii::t('app', 'Who Brings Buyer'),
            'seller_id' => Yii::t('app', 'Seller'),
            'is_new_seller' => Yii::t('app', 'The Seller Is A New Client'),
            'seller_provider' => Yii::t('app', 'Who Brings Seller'),
            'lead_type' => Yii::t('app', 'Lead Type'),
            'search_started_at' => Yii::t('app', 'Search Started Date'),
            'suggested_sale_price_eu' => Yii::t('app', 'Suggested Sale Price'),
            'passed_to_sales_by' => Yii::t('app', 'Passed To Sales By'),
            'property_id' => Yii::t('app', 'Property'),
            'is_home_staged' => Yii::t('app', 'The Home Is Staged'),
            'our_fee_eu' => Yii::t('app', 'Our Fee'),
            'their_fee_eu' => Yii::t('app', 'Their Fee'),
            'payrolled_at' => Yii::t('app', 'Payrolled Date'),
            'comments' => Yii::t('app', 'Comments'),
            'approved' => Yii::t('app', 'Approved'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getInvoices()
    {
        return $this->hasMany(Invoice::className(), ['transaction_id' => 'id']);
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

    public function afterFind() {
        parent::afterFind();
        $formatter = Yii::$app->formatter;
        $this->sale_price_eu = round($this->sale_price_euc / 100., 2);
        $this->first_published_price_eu = round($this->first_published_price_euc / 100., 2);
        $this->last_published_price_eu = round($this->last_published_price_euc / 100., 2);
        $this->sale_price_eu = round($this->sale_price_euc / 100., 2);
        $this->suggested_sale_price_eu = round($this->suggested_sale_price_euc / 100., 2);
        $this->our_fee_eu = round($this->our_fee_euc / 100., 2);
        $this->their_fee_eu = round($this->their_fee_euc / 100., 2);
        
    }
    public function beforeValidate() {
        if (!$this->sale_price_eu) $this->sale_price_eu = null;
        return parent::beforeValidate();
    }
    public function beforeSave($insert) {
        $this->sale_price_euc = round($this->sale_price_eu * 100.);
        $this->first_published_price_euc = round($this->first_published_price_eu * 100.);
        if (!$this->first_published_price_euc ) $this->first_published_price_euc = null;
        $this->last_published_price_euc = round($this->last_published_price_eu * 100.);
        if (!$this->last_published_price_euc) $this->last_published_price_euc = null;
        $this->suggested_sale_price_euc = round($this->suggested_sale_price_eu * 100.);
        if (!$this->suggested_sale_price_euc ) $this->suggested_sale_price_euc = null;
        $this->our_fee_euc = round($this->our_fee_eu * 100.);
        $this->their_fee_euc = round($this->their_fee_eu * 100.);
        $this->updated_at = date('Y-m-d H:i:s');
        if ($insert) $this->created_at = $this->updated_at;
        return parent::beforeSave($insert);
    }
}
