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
    public static function getAttributionSumOnOptionDate($from, $to, $sum_alias = 'sum', $count_alias = 'count', $not_attributed = 'NA')
    {
        return static::find()
            ->joinWith(['effectiveAttributions.transaction' => function($q) use ($from, $to) {
                $q->where('transaction.id is null or option_signed_at between :from and :to', [
                    ':from' => $from,
                    ':to' => $to
                ]);
            }])->join('full join', '(
                select sum(our_fee_euc) - sum(tx_attribution.sum) as sum, :na1::varchar as name
                from transaction left join (
                    select sum(ea.amount_euc), transaction_id
                    from effective_attribution ea join
                        transaction t on t.id = ea.transaction_id
                    where option_signed_at between :from_na1 and :to_na1
                    group by transaction_id) tx_attribution on transaction.id = transaction_id
                where our_fee_euc > 0 and option_signed_at between :from_na2 and :to_na2) not_attributed', 'false', [
                ':from_na1' => $from,
                ':to_na1' => $to,
                ':from_na2' => $from,
                ':to_na2' => $to,
                ':na1' => $not_attributed
            ])->select([
                'coalesce(advisor.name, not_attributed.name) as name',
                "round(sum((coalesce(amount_euc, 0) + coalesce(not_attributed.sum, 0)) / 100.), 2) as {$sum_alias}", "count(*) as {$count_alias}"
            ])
            ->orderBy('name')
            ->groupBy(['coalesce(advisor.name, not_attributed.name)'])
            ->having("round(sum((coalesce(amount_euc, 0) + coalesce(not_attributed.sum, 0)) / 100.), 2) > 0")
            ->createCommand()->queryAll();
    }
    /**
     */
    public static function getAttributionSumOnInvoiceDate($from, $to, $sum_alias = 'sum', $count_alias = 'count', $not_attributed = 'NA')
    {
        $min_issued_at = static::find()
            ->innerJoinWith('effectiveAttributions.transaction.invoices')->min('issued_at');
        if (!$min_issued_at) $min_issued_at = date('Y-m-d'); 
        else $min_issued_at = date('Y-m-01', strtotime($min_issued_at));
        if ($min_issued_at < $from) $min_issued_at = $from;
        $archive_to = date('Y-m-d', strtotime($min_issued_at) - 1);
        if ($archive_to > $to) $archive_to = $to;
        $subquery = static::find()
            ->innerJoinWith(['effectiveAttributions.transaction' => function($q) use ($min_issued_at, $to) {
                $q->innerJoin('(
                    select sum(amount_euc), transaction_id
                    from invoice
                    where issued_at between :from1 and :to1
                    group by transaction_id
                ) period_invoice', 'transaction.id = period_invoice.transaction_id', [
                    ':from1' => $min_issued_at,
                    ':to1' => $to
                ]);
            }])->join('cross join', '(
                select sum(amount_euc)
                from invoice
                where issued_at between :from2 and :to2
            ) invoiced', null, [
                ':from2' => $min_issued_at,
                ':to2' => $to
            ])->join('cross join', '(
                select true as attributed
                union
                select null
            ) u')->join('full join', '(
                select sum(attributed_euc), coalesce(name, :na3) as name
                from archived_attribution
                     join advisor on advisor.id = archived_attribution.advisor_id
                     join archived_invoice on archived_attribution.archived_invoice_id = archived_invoice.id 
                where month between :from3 and :to3
                group by name
            ) archived', 'false', [
                ':from3' => $from,
                ':to3' => $archive_to,
                ':na3' => $not_attributed
            ])->join('full join', '(
                select sum(amount_euc) - sum(invoice_attribution.sum) as sum, :na::varchar as name
                from archived_invoice ai join (
                    select archived_invoice_id, sum(attributed_euc)
                    from archived_attribution join
                        archived_invoice on archived_invoice_id = archived_invoice.id
                    where month between :from_na1 and :to_na1 and
                        attributed_euc <> 0
                    group by archived_invoice_id) invoice_attribution on (archived_invoice_id = ai.id)
                where month between :from_na2 and :to_na2) archived_not_attributed', 'false', [
                ':from_na1' => $from,
                ':to_na1' => $archive_to,
                ':from_na2' => $from,
                ':to_na2' => $archive_to,
                ':na' => $not_attributed
            ])->select([
                'case when attributed then
                     coalesce(advisor.name, archived.name, :na4)
                 else 
                     coalesce(archived.name, archived_not_attributed.name, :na5)
                 end as joint_name',
                "case when attributed then
                     round(sum(coalesce(effective_attribution.amount_euc, 0))/ 100., 2)
                 else
                     round((coalesce(invoiced.sum, 0) + sum(coalesce(-effective_attribution.amount_euc, 0) + coalesce(archived_not_attributed.sum, 0) + coalesce(archived.sum, 0))) / 100., 2)
                 end as {$sum_alias}",
            ])->having("round(sum(coalesce(effective_attribution.amount_euc, 0) + coalesce(archived_not_attributed.sum, 0) + coalesce(archived.sum, 0))/ 100., 2) > 0")
            ->orderBy('joint_name')
            ->groupBy(['joint_name', 'attributed', 'invoiced.sum']);
        $subquery->addParams([':na4' => $not_attributed, ':na5' => $not_attributed]);
        $query = (new \yii\db\Query)->from(['aux' => $subquery]);
        $query->select(['joint_name', "sum($sum_alias) as $sum_alias"])->groupBy('joint_name');
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
    public static function getAttributionOverOperationCount($from, $to, $sum_alias = 'sum', $count_alias = 'count', $not_attributed = 'na')
    {
        $query1 = static::find()
            ->joinWith(['effectiveAttributions.transaction' => function($q) use ($from, $to) {
                $q->where('option_signed_at between :from1 and :to1', [
                    ':from1' => $from,
                    ':to1' => $to
                ])->andWhere(['>', 'effective_attribution.amount_euc', 0]);
            }])->innerJoin('(
                select advisor_id, count(*)
                from (
                    select distinct on (transaction_id) transaction_id, advisor_id
                    from effective_attribution) ea join 
                    transaction t on (t.id = ea.transaction_id) join
                    advisor ad on ea.advisor_id = ad.id 
                where option_signed_at between :from2 and :to2 group by advisor_id) ea_tx', 'ea_tx.advisor_id = advisor.id', [ 
                ':from2' => $from,
                ':to2' => $to
            ])->select(['advisor.name', "round(sum(amount_euc)/ea_tx.count / 100, 2) as {$sum_alias}", "count(*) as {$count_alias}"])
            ->orderBy('advisor.name')
            ->groupBy('advisor.name, ea_tx.count');
        $query2 = (new \yii\db\Query())
            ->select(['(:na::varchar) as name', "round(sum(our_fee_euc)/100./count(*), 2) as {$sum_alias}", "count(*) as {$count_alias}"])
            ->from('transaction')
            ->where('our_fee_euc > 0 and option_signed_at between :from3 and :to3')
            ->addParams([':na' => $not_attributed, ':from3' => $from, ':to3' => $to]);
        return $query1->union($query2, false)->createCommand()->queryAll();
    }
}
