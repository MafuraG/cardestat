<?php

namespace app\models;

use Yii;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "advisor".
 *
 * @property integer $id
 * @property string $name
 * @property string $default_office
 * @property integer $default_attribution_type_id
 *
 * @property AttributionType $defaultAttributionType
 * @property Office $defaultOffice
 * @property Tranche[] $tranches
 * @property Attribution[] $attributions
 * @property Transaction[] $transactions
 */
class Advisor extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'advisor';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['is_hub_agent', 'active'], 'boolean'],
            [['default_attribution_type_id'], 'integer'],
            [['name'], 'string', 'max' => 32],
            [['default_office'], 'string', 'max' => 18],
            [['name'], 'unique'],
            [['default_attribution_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => AttributionType::className(), 'targetAttribute' => ['default_attribution_type_id' => 'id']],
            [['default_office'], 'exist', 'skipOnError' => true, 'targetClass' => Office::className(), 'targetAttribute' => ['default_office' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'is_hub_agent' => Yii::t('app', 'Lead Hub'),
            'active' => Yii::t('app', 'Active'),
            'default_office' => Yii::t('app', 'Default Office'),
            'default_attribution_type_id' => Yii::t('app', 'Default Attribution Type'),
        ];
    }

    public function afterFind()
    {
        parent::afterFind();
        if ($this->id and !$this->tranches) {
            $tranche = new AdvisorTranche();
            $tranche->from_euc = 0;
            $tranche->commission_bp = 0;
            $tranche->advisor_id = $this->id;
            $tranche->save(false);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultAttributionType()
    {
        return $this->hasOne(AttributionType::className(), ['id' => 'default_attribution_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultOffice()
    {
        return $this->hasOne(Office::className(), ['name' => 'default_office']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTranches()
    {
        return $this->hasMany(AdvisorTranche::className(), ['advisor_id' => 'id'])
            ->orderBy(['from_euc' => SORT_ASC]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEffectiveAttributions()
    {
        return $this->hasMany(EffectiveAttribution::className(), ['advisor_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributions()
    {
        return $this->hasMany(Attribution::className(), ['advisor_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(Transaction::className(), ['passed_to_sales_by' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArchivedAttributions()
    {
        return $this->hasMany(ArchivedAttribution::className(), ['advisor_id' => 'id']);
    }

    /**
     */
    public static function listActiveHub()
    {
        return ArrayHelper::map(static::find()->where(['is_hub_agent' => true, 'active' => true])->orderBy('name')->all(), 'id', 'name');
    }

    /**
     */
    public static function listActive()
    {
        return ArrayHelper::map(static::find()->where(['active' => true])->orderBy('name')->all(), 'id', 'name');
    }

    /**
     */
    public static function listAll()
    {
        return ArrayHelper::map(static::find()->orderBy('name')->all(), 'id', 'name');
    }
    /**
     */
    public static function getAttributionSum($from, $to, $sum_alias = 'sum', $count_alias = 'count')
    {
        $min_issued_at = static::find()
            ->innerJoinWith('effectiveAttributions.transaction.invoices')->min('issued_at');
        if (!$min_issued_at) $min_issued_at = date('Y-m-d'); 
        else $min_issued_at = date('Y-m-01', strtotime($min_issued_at));
        if ($min_issued_at < $from) $min_issued_at = $from;
        $archive_to = date('Y-m-d', strtotime($min_issued_at) - 1);
        $query = static::find()
            ->innerJoinWith(['effectiveAttributions.transaction' => function($q) use ($min_issued_at, $to) {
                $q->innerJoin('(
                    select min(issued_at) issued_at, transaction_id
                    from invoice
                    where issued_at between :from and :to
                    group by transaction_id
                ) oldest_invoice', 'transaction.id = oldest_invoice.transaction_id', [
                    ':from' => $min_issued_at,
                    ':to' => $to
                ]);
            }])->join('full join', '(
                select sum(attributed_euc), name
                from archived_attribution
                     join advisor on advisor.id = archived_attribution.advisor_id
                     join archived_invoice on archived_attribution.archived_invoice_id = archived_invoice.id 
                where month between :from2 and :to2
                group by name
            ) archived', 'false', [
                ':from2' => $from,
                ':to2' => $archive_to
            ])->select([
                '(case when advisor.name is null
                    then archived.name 
                 else advisor.name
                 end) as joined_name',
                "round(sum((coalesce(effective_attribution.amount_euc, 0) + coalesce(archived.sum, 0))/ 100.), 2) as {$sum_alias}",
                "count(*) as {$count_alias}"
            ])->orderBy('joined_name')
            ->groupBy('joined_name');
        return $query->createCommand()->queryAll();
    }
    /**
     */
    public static function getProratedOperationCount($from, $to, $sum_alias = 'sum', $count_alias = 'count')
    {
        return static::find()
            ->joinWith(['effectiveAttributions.attributionType', 'effectiveAttributions.transaction' => function($q) use ($from, $to) {
                $q->where('option_signed_at between :from and :to', [
                    ':from' => $from,
                    ':to' => $to
                ])->andWhere(['<>', 'attribution_bp', 0]);
            }])->select(['advisor.name', "round(sum(attribution_type.attribution_bp / 10000.), 4) as {$sum_alias}", "count(*) as {$count_alias}"])
            ->orderBy('advisor.name')
            ->groupBy('advisor.name')
            ->createCommand()->queryAll();
    }
    /**
     */
    public static function getAttributionOverOperationCount($from, $to, $sum_alias = 'sum', $count_alias = 'count')
    {
        return static::find()
            ->joinWith(['effectiveAttributions.attributionType', 'effectiveAttributions.transaction' => function($q) use ($from, $to) {
                $q->where('option_signed_at between :from and :to', [
                    ':from' => $from,
                    ':to' => $to
                ])->andWhere(['<>', 'attribution_bp', 0]);
            }])->select(['advisor.name', "round(sum(amount_euc)/count(*) / 100, 2) as {$sum_alias}", "count(*) as {$count_alias}"])
            ->orderBy('advisor.name')
            ->groupBy('advisor.name')
            ->createCommand()->queryAll();
    }
}
