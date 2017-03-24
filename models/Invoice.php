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
}
