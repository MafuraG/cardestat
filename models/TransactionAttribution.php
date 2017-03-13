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
class TransactionAttribution extends Attribution
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'transaction_attribution';
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAttribution()
    {
        return $this->hasOne(Attribution::className(), ['id' => 'id']);
    }

}
