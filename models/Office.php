<?php

namespace app\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "office".
 *
 * @property string $name
 *
 * @property Advisor[] $advisors
 * @property Attribution[] $attributions
 */
class Office extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'office';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'string', 'max' => 18],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('app', 'Name'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvisors()
    {
        return $this->hasMany(Advisor::className(), ['default_office' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEffectiveAttributions()
    {
        return $this->hasMany(EffectiveAttribution::className(), ['office' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributions()
    {
        return $this->hasMany(Attribution::className(), ['office' => 'name']);
    }

    public static function listAll()
    {
        return ArrayHelper::map(static::find()->orderBy('name')->all(), 'name', 'name');
    }
    /**
     */
    public static function getAttributionSumOnInvoiceDate($from, $to, $sum_alias = 'sum', $count_alias = 'count', $no_office = 'NA', $not_attributed = 'NA')
    {
        $min_issued_at = static::find()
            ->innerJoinWith('effectiveAttributions.transaction.invoices')->min('issued_at');
        if (!$min_issued_at) $min_issued_at = date('Y-m-d'); 
        else $min_issued_at = date('Y-m-01', strtotime($min_issued_at));
        if ($min_issued_at < $from) $min_issued_at = $from;
        $archive_to = date('Y-m-d', strtotime($min_issued_at) - 1);
        if ($archive_to > $to) $archive_to = $to;
        return static::find()
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
                select sum(amount_euc), :na_no::varchar as name
                from effective_attribution ea join
                     transaction t on (ea.transaction_id = t.id) join (
                         select distinct transaction_id
                         from invoice
                         where issued_at between :from_no and :to_no
                         group by transaction_id) invoice_no on t.id = invoice_no.transaction_id
                where office is null) wo_office', 'false', [
                ':from_no' => $min_issued_at,
                ':to_no' => $to,
                ':na_no' => $no_office
            ])->join('full join', '(
                select sum(amount_euc) - sum(invoice_attribution.sum) as sum, :na1::varchar as name
                from invoice left join (
                    select sum(ea.amount_euc), i.id as invoice_id
                    from effective_attribution ea join
                        transaction t on (ea.transaction_id = t.id) join (
                            select distinct on (transaction_id) *
                            from invoice
                            where issued_at between :from_na3 and :to_na3) i on (i.transaction_id = t.id)
                    group by i.id) invoice_attribution on invoice.id = invoice_id
                where issued_at between :from_na4 and :to_na4) not_attributed', 'false', [
                ':from_na3' => $min_issued_at,
                ':to_na3' => $to,
                ':from_na4' => $min_issued_at,
                ':to_na4' => $to,
                ':na1' => $not_attributed
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
            ])->join('full join', '(
                select sum(attributed_euc), coalesce(office, :no) as name
                from archived_attribution
                     join archived_invoice on archived_attribution.archived_invoice_id = archived_invoice.id 
                where month between :from2 and :to2
                group by name
            ) archived', 'false', [
                ':from2' => $from,
                ':to2' => $archive_to,
                ':no' => $no_office
            ])->select([
                'coalesce(office.name, wo_office.name, archived.name, archived_not_attributed.name, not_attributed.name) as joint_name',
                "round(sum((coalesce(effective_attribution.amount_euc, 0) + coalesce(wo_office.sum, 0) + coalesce(archived.sum, 0) + coalesce(not_attributed.sum, 0) + coalesce(archived_not_attributed.sum, 0))/ 100.), 2) as {$sum_alias}",
                "count(*) as {$count_alias}"
            ])->orderBy('joint_name')
            ->groupBy('joint_name')
            ->createCommand()->queryAll();
    }
    /**
     */
    public static function getAttributionSumOnOptionDate($from, $to, $sum_alias = 'sum', $count_alias = 'count', $not_attributed = 'NA', $no_office = 'NO')
    {
        return static::find()
            ->joinWith(['effectiveAttributions.transaction' => function($q) use ($from, $to) {
                $q->where('transaction.id is null or option_signed_at between :from and :to', [
                    ':from' => $from,
                    ':to' => $to
                ]);
            }])->join('full join', '(
                select sum(amount_euc), :na_no::varchar as name
                from effective_attribution ea join
                     transaction t on (ea.transaction_id = t.id)
                where our_fee_euc > 0 and
                    option_signed_at between :from_no and :to_no and
                    office is null) wo_office', 'false', [
                ':from_no' => $from,
                ':to_no' => $to,
                ':na_no' => $no_office
            ])->join('full join', '(
                select greatest(0, sum(our_fee_euc) - sum(tx_attribution.sum)) as sum, :na1::varchar as name
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
                'coalesce(office.name, not_attributed.name, wo_office.name) as name',
                "round(sum((coalesce(amount_euc, 0) + coalesce(wo_office.sum, 0) + coalesce(not_attributed.sum, 0)) / 100.), 2) as {$sum_alias}", "count(*) as {$count_alias}"
            ])->orderBy('name')
            ->groupBy(['coalesce(office.name, not_attributed.name, wo_office.name)'])
            ->createCommand()->queryAll();
    }

    /**
     */
    public static function getProratedOperationCount($from, $to, $sum_alias = 'sum', $count_alias = 'count')
    {
        return static::find()
            ->joinWith(['effectiveAttributions.attributionType', 'attributions.transaction' => function($q) use ($from, $to) {
                $q->where('option_signed_at between :from and :to', [
                    ':from' => $from,
                    ':to' => $to
                ]);
            }])->select(['office.name', "round(sum(attribution_type.attribution_bp / 10000.), 4) as {$sum_alias}", "count(*) as {$count_alias}"])
            ->orderBy('office.name')
            ->groupBy('office.name')
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
                ]);
            }])->select(['office.name', "round(sum(amount_euc)/count(*) / 100, 2) as {$sum_alias}", "count(*) as {$count_alias}"])
            ->orderBy('office.name')
            ->groupBy('office.name')
            ->createCommand()->queryAll();
    }
}
