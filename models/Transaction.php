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
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "transaction".
 *
 * @property integer $id
 * @property string $external_id
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
 * @property Payroll[] $payrolls
 */
class Transaction extends \yii\db\ActiveRecord
{
    public $first_published_price_eu;
    public $last_published_price_eu;
    public $sale_price_eu;
    public $suggested_sale_price_eu;
    public $our_fee_eu;
    public $their_fee_eu;
    private $_dbTransaction;
    
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transaction';
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
            ['external_id', 'unique'],
            [['first_published_at', 'last_published_at', 'option_signed_at', 'search_started_at', 'payroll_month'], 'date', 'format' => 'yyyy-MM-dd'],
            ['payroll_month', 'checkPayrollMonth'],
            [['first_published_price_eu', 'last_published_price_eu', 'sale_price_eu', 'suggested_sale_price_eu', 'our_fee_eu', 'their_fee_eu'], 'number'],
            [['buyer_id', 'seller_id', 'passed_to_sales_by', 'property_id'], 'integer'],
            [['transaction_type', 'option_signed_at', 'buyer_id', 'seller_id', 'property_id', 'sale_price_eu'], 'required'],
            [['approved_by_accounting'], 'boolean', 'on' => 'accounting'],
            [['approved_by_direction'], 'boolean', 'on' => 'admin'],
            [['is_new_buyer', 'is_new_seller', 'is_home_staged'], 'boolean'],
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
            [['lead_type', 'custom_type', 'transfer_type', 'transaction_type', 'development_type', 'seller_provider', 'buyer_provider', 'external_id', 'seller_id', 'buyer_id', 'property_id'], 'default', 'value' => null],
        ];
    }

    public function checkPayrollMonth($attribute, $params) 
    {
        $advisor_ids = ArrayHelper::getColumn($this->attributions, 'advisor_id');
        $n = Payroll::find()->where([
            'month' => $this->payroll_month,
            'advisor_id' => $advisor_ids
        ])->andWhere(['not', ['commission_bp' => null]])->count();
        if ($n > 0) $this->addError($attribute, Yii::t('app', 'Some adviser has its payroll closed already for this month.'));
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'external_id' => Yii::t('app', 'External ID'),
            'transaction_type' => Yii::t('app', 'Transaction Type'),
            'custom_type' => Yii::t('app', 'Custom Type'),
            'transfer_type' => Yii::t('app', 'Transfer Type'),
            'development_type' => Yii::t('app', 'Development Type'),
            'first_published_at' => Yii::t('app', 'First Published Date'),
            'first_published_price_eu' => Yii::t('app', 'First Published Price'),
            'last_published_at' => Yii::t('app', 'Last Published Date'),
            'last_published_price_eu' => Yii::t('app', 'Last Published Price'),
            'option_signed_at' => Yii::t('app', 'Option/Reservation Date'),
            'sale_price_eu' => Yii::t('app', 'Sale/Rental Price'),
            'buyer_id' => Yii::t('app', 'Buyer/Tenant'),
            'is_new_buyer' => Yii::t('app', 'The Buyer/Tenant Is New Client'),
            'buyer_provider' => Yii::t('app', 'Who Brings Buyer/Tenant'),
            'seller_id' => Yii::t('app', 'Seller/Landlord'),
            'is_new_seller' => Yii::t('app', 'The Seller/Landlord Is New Client'),
            'seller_provider' => Yii::t('app', 'Who Brings Seller/Landlord'),
            'lead_type' => Yii::t('app', 'Lead Type'),
            'search_started_at' => Yii::t('app', 'Search Started Date'),
            'suggested_sale_price_eu' => Yii::t('app', 'Suggested Price'),
            'passed_to_sales_by' => Yii::t('app', 'Passed To Sales By'),
            'property_id' => Yii::t('app', 'Property'),
            'is_home_staged' => Yii::t('app', 'Home Staging'),
            'our_fee_eu' => Yii::t('app', 'Our Fees'),
            'their_fee_eu' => Yii::t('app', 'Collaborator\'s Fee'),
            'payroll_month' => Yii::t('app', 'Payroll Month'),
            'comments' => Yii::t('app', 'Internal Comments'),
            'approved_by_direction' => Yii::t('app', 'Direc. Apprv.'),
            'approved_by_accounting' => Yii::t('app', 'Account. Apprv.'),
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
    public function getPayrolledTransactions()
    {
        return $this->hasMany(PayrolledTransaction::className(), ['transaction_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributionSummary()
    {
        return $this->hasOne(TransactionAttributionSummary::className(), ['transaction_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEffectiveAttributions()
    {
        return $this->hasMany(EffectiveAttribution::className(), ['transaction_id' => 'id']);
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
        if ($this->payroll_month) $this->payroll_month = substr($this->payroll_month, 0, 7) . '-01';
        return parent::beforeValidate();
    }
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        try {
            if (array_key_exists('payroll_month', $changedAttributes) and
                $changedAttributes['payroll_month'] !== $this->payroll_month) {
                if ($this->payroll_month) {
                    $effective_attributions = $this->getEffectiveAttributions()->with('tranches')->all();
                    $payrolls = [];
                    foreach ($effective_attributions as $effective_attribution) {
                        $attribution = $effective_attribution->attribution;
                        $advisor_id = $attribution->advisor_id;
                        $payrolls[$advisor_id] = Payroll::findOne([
                            'advisor_id' => $advisor_id,
                            'month' => $this->payroll_month
                        ]);
                        if (!isset($payrolls[$advisor_id])) {
                            $payrolls[$advisor_id] = new Payroll([
                                'advisor_id' => $advisor_id,
                                'month' => $this->payroll_month
                            ]);
                            if (!$payrolls[$advisor_id]->save()) {
                                $msg = var_export($payrolls[$advisor_id]->errors, 1);
                                throw new \Exception($msg);
                            }
                        }
                        $attribution->amount_euc = $effective_attribution->amount_euc;
                        $attribution->payroll_id = $payrolls[$advisor_id]->id;
                        if (!$attribution->save(false)) {
                            $msg = var_export($attribution->errors, 1);
                            throw new \Exception($msg);
                        }
                    }
                } else {
                    foreach ($this->attributions as $attribution) {
                        $attribution->payroll_id = null;
                        $attribution->amount_euc = null;
                        if (!$attribution->save(false)) {
                            $msg = var_export($attribution->errors, 1);
                            throw new \Exception($msg);
                        }
                    }
                    foreach (Payroll::find()->joinWith('attributions')
                        ->where(['payroll_id' => null])->each() as $zombie_payroll) $zombie_payroll->delete();
                }
            }
            $this->_dbTransaction->commit();
        } catch (\Exception $e) {
            $this->_dbTransaction->rollback();
            throw new \yii\web\HttpException(500, $e->getMessage());
        }
    }
    public function beforeSave($insert) {
        $this->_dbTransaction = Yii::$app->db->beginTransaction();
        $this->sale_price_euc = round($this->sale_price_eu * 100.);
        $this->first_published_price_euc = round($this->first_published_price_eu * 100.);
        if (!$this->first_published_price_euc ) $this->first_published_price_euc = null;
        $this->last_published_price_euc = round($this->last_published_price_eu * 100.);
        if (!$this->last_published_price_euc) $this->last_published_price_euc = null;
        $this->suggested_sale_price_euc = round($this->suggested_sale_price_eu * 100.);
        if (!$this->suggested_sale_price_euc ) $this->suggested_sale_price_euc = null;
        $this->our_fee_euc = round($this->our_fee_eu * 100.);
        $this->their_fee_euc = round($this->their_fee_eu * 100.);
        return parent::beforeSave($insert);
    }
    public static function getVolume($from = null, $to = null, $months = 1, $transaction_type = null, $sum_alias = 'sum', $avg = false)
    {
        if ($months == 12) $interval = 'year';
        else if ($months == 3) $interval = 'quarter';
        else $interval = 'month';
        $joinArgs = [
            ':arg0' => $interval,
            ':arg1' => $from,
            ':arg2' => $to,
            ':arg3' => "$months month",
            ':arg4' => $interval,
            ':arg5' => $from,
            ':arg6' => $to,
        ];
        $joinCondition = "series.period = date_trunc(:arg4, first_invoiced_at)::date and first_invoiced_at between :arg5 and :arg6";
        if ($transaction_type) {
            $joinCondition .=  ' and transaction_type = :arg7';
            $joinArgs[':arg7'] = $transaction_type;
        }
        $query = static::find();
        $query->innerJoin('(
            select transaction_id, min(issued_at) as first_invoiced_at
            from invoice
            where issued_at between :arg8 and :arg9
            group by transaction_id) tx_invoice', 'tx_invoice.transaction_id = transaction.id', [
            ':arg8' => $from,
            ':arg9' => $to
        ]);
        if ($avg) $query->select(['period', "round(sum(sale_price_euc)/count(*) / 100., 2) as {$sum_alias}"]);
        else $query->select(['period', "round(sum(sale_price_euc) / 100., 2) as {$sum_alias}"]);
        return $query->rightJoin("(
                select date_trunc(:arg0, d)::date as period
                from generate_series(:arg1::date, :arg2, :arg3) d) series", $joinCondition, $joinArgs)
            ->groupBy('period')
            ->orderBy('period')
            ->createCommand()->queryAll();
    }
    public static function getAvgVolume($from = null, $to = null, $months = 1, $transaction_type = null, $sum_alias = 'sum')
    {
        return static::getVolume($from, $to, $months, $transaction_type, $sum_alias, true);
    }
    public static function getCount($from = null, $to = null, $months = 1, $transaction_type = null, $sum_alias = 'sum', $shared = false)
    {
        if ($months == 12) $interval = 'year';
        else if ($months == 3) $interval = 'quarter';
        else $interval = 'month';
        $joinArgs = [
            ':arg0' => $interval,
            ':arg1' => $from,
            ':arg2' => $to,
            ':arg3' => "$months month",
            ':arg4' => $interval,
            ':arg5' => $from,
            ':arg6' => $to,
        ];
        $joinCondition = "series.period = date_trunc(:arg4, option_signed_at)::date and option_signed_at between :arg5 and :arg6";
        if ($transaction_type) {
            $joinCondition .=  ' and transaction_type = :arg7';
            $joinArgs[':arg7'] = $transaction_type;
        }
        $query = static::find()
            ->select(['period', "case when sum(id) is null then 0 else count(*) end as {$sum_alias}"])
            ->rightJoin("(
                select date_trunc(:arg0, d)::date as period
                from generate_series(:arg1::date, :arg2, :arg3) d) series", $joinCondition, $joinArgs)
            ->groupBy('period')
            ->orderBy('period');
        if ($shared) $query->where(['or',
            ['not', ['our_fee_euc' => null]],
            ['<=', 'our_fee_euc', 0]
        ])->andWhere(['or',
            ['not', ['their_fee_euc' => null]],
            ['<=', 'their_fee_euc', 0]
        ]);
        return $query->createCommand()->queryAll();
    }
    public static function countAll($from = null, $to = null, $months = 1, $transaction_type = null, $sum_alias = 'sum')
    {
        return static::getCount($from, $to, $months, $transaction_type, $sum_alias, false);
    }
    public static function countShared($from = null, $to = null, $months = 1, $transaction_type = null, $sum_alias = 'sum')
    {
        return static::getCount($from, $to, $months, $transaction_type, $sum_alias, true);
    }
}
