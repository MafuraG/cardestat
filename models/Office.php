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
    public static function getAttributionSum($from, $to, $sum_alias = 'sum', $count_alias = 'count')
    {
        return static::find()
            ->joinWith(['effectiveAttributions.transaction' => function($q) use ($from, $to) {
                $q->where('option_signed_at between :from and :to', [
                    ':from' => $from,
                    ':to' => $to
                ]);
            }])->select(['name', "round(sum(amount_euc / 100.), 2) as {$sum_alias}", "count(*) as {$count_alias}"])
            ->orderBy('name')
            ->groupBy('name')
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
