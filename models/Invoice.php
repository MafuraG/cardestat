<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "invoice".
 *
 * @property string $code
 * @property string $issued_at
 * @property integer $amount_euc
 * @property string $recipient_category
 *
 * @property RecipientCategory $recipientCategory
 */
class Invoice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'invoice';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['transaction_id', 'exist', 'skipOnError' => true, 'targetClass' => Transaction::className(), 'targetAttribute' => ['transaction_id' => 'id']],
            [['code', 'recipient_category', 'issued_at', 'amount_euc'], 'required'],
            [['issued_at'], 'safe'],
            [['amount_euc'], 'integer'],
            [['code', 'recipient_category'], 'string', 'max' => 18],
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
            'issued_at' => Yii::t('app', 'Issued At'),
            'amount_euc' => Yii::t('app', 'Amount Euc'),
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
}
