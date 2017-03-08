<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "attribution".
 *
 * @property integer $id
 * @property integer $advisor_id
 * @property string $office
 * @property integer $attribution_type_id
 * @property integer $amount_euc
 * @property integer $transaction_id
 * @property string $comments
 *
 * @property Advisor $advisor
 * @property AttributionType $attributionType
 * @property Office $office0
 * @property Transaction $transaction
 */
class Attribution extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'attribution';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['advisor_id', 'attribution_type_id', 'amount_euc', 'transaction_id'], 'required'],
            [['advisor_id', 'attribution_type_id', 'amount_euc', 'transaction_id'], 'integer'],
            [['comments'], 'string'],
            [['office'], 'string', 'max' => 18],
            [['advisor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Advisor::className(), 'targetAttribute' => ['advisor_id' => 'id']],
            [['attribution_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => AttributionType::className(), 'targetAttribute' => ['attribution_type_id' => 'id']],
            [['office'], 'exist', 'skipOnError' => true, 'targetClass' => Office::className(), 'targetAttribute' => ['office' => 'name']],
            [['transaction_id'], 'exist', 'skipOnError' => true, 'targetClass' => Transaction::className(), 'targetAttribute' => ['transaction_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'advisor_id' => Yii::t('app', 'Advisor ID'),
            'office' => Yii::t('app', 'Office'),
            'attribution_type_id' => Yii::t('app', 'Attribution Type ID'),
            'amount_euc' => Yii::t('app', 'Amount Euc'),
            'transaction_id' => Yii::t('app', 'Transaction ID'),
            'comments' => Yii::t('app', 'Comments'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAdvisor()
    {
        return $this->hasOne(Advisor::className(), ['id' => 'advisor_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttributionType()
    {
        return $this->hasOne(AttributionType::className(), ['id' => 'attribution_type_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOffice0()
    {
        return $this->hasOne(Office::className(), ['name' => 'office']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransaction()
    {
        return $this->hasOne(Transaction::className(), ['id' => 'transaction_id']);
    }
}
