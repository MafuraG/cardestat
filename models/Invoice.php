<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "invoice".
 *
 * @property string $code
 * @property string $issued_at
 * @property integer $amount_euc
 * @property string $recipient_category
 * @property string $created_at
 * @property string $updated_at
 *
 * @property RecipientCategory $recipientCategory
 */
class Invoice extends \yii\db\ActiveRecord
{
    public $amount_eu;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invoice';
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
            ['transaction_id', 'exist', 'skipOnError' => true, 'targetClass' => Transaction::className(), 'targetAttribute' => ['transaction_id' => 'id']],
            [['code', 'recipient_category', 'issued_at', 'amount_eu'], 'required'],
            [['issued_at'], 'safe'],
            [['amount_eu'], 'number'],
            [['code', 'recipient_category'], 'string', 'max' => 18],
            ['code', 'unique'],
            [['recipient_category'], 'exist', 'skipOnError' => true, 'targetClass' => RecipientCategory::className(), 'targetAttribute' => ['recipient_category' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'code' => Yii::t('app', 'Code'),
            'issued_at' => Yii::t('app', 'Issue Date'),
            'amount_eu' => Yii::t('app', 'Amount'),
            'recipient_category' => Yii::t('app', 'Recipient Category'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransaction()
    {
        return $this->hasOne(Transaction::className(), ['id' => 'transaction_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRecipientCategory()
    {
        return $this->hasOne(RecipientCategory::className(), ['name' => 'recipient_category']);
    }

    public function afterFind() {
        parent::afterFind();
        $formatter = Yii::$app->formatter;
        $this->amount_eu = round($this->amount_euc / 100., 2);
        
    }
    public function beforeValidate() {
        if (!$this->amount_eu) $this->amount_eu = null;
        return parent::beforeValidate();
    }
    public function beforeSave($insert) {
        $this->amount_euc = round($this->amount_eu * 100.);
        return parent::beforeSave($insert);
    }
    public static function getRevenue($from = null, $to = null, $months = 1, $transaction_type = null, $sum_alias = 'sum')
    {
        if ($months == 12) $interval = 'year';
        else if ($months == 3) $interval = 'quarter';
        else $interval = 'month';
        $query = static::find()
            ->select(['period', "round(sum(amount_euc) / 100., 2) as {$sum_alias}"])
            ->rightJoin("(
                select date_trunc(:arg0, d)::date as period 
                from generate_series(:arg1::date, :arg2, :arg3) d) series",
                "series.period = date_trunc(:arg4, issued_at)::date and issued_at between :arg5 and :arg6", [
                ':arg0' => $interval,
                ':arg1' => $from,
                ':arg2' => $to,
                ':arg3' => "$months month",
                ':arg4' => $interval,
                ':arg5' => $from,
                ':arg6' => $to,
            ])->groupBy('period')
            ->orderBy('period');
        if ($transaction_type) $query->innerJoinWith(['transaction' => function($q) use ($transaction_type) {
            $q->onCondition(['transaction_type' => $transaction_type]);
        }]);
        return $query->createCommand()->queryAll();
    }
}
