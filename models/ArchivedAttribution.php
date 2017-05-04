<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "archived_attribution".
 *
 * @property integer $id
 * @property integer $archived_invoice_id
 * @property integer $attributed_euc
 * @property integer $advisor_id
 * @property integer $commission_euc
 * @property integer $n_operations_c
 *
 * @property Advisor $advisor
 * @property ArchivedInvoice $archivedInvoice
 */
class ArchivedAttribution extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'archived_attribution';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['archived_invoice_id', 'attributed_euc', 'advisor_id', 'commission_euc', 'n_operations_c'], 'integer'],
            [['attributed_euc', 'advisor_id', 'commission_euc', 'n_operations_c'], 'required'],
            [['advisor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Advisor::className(), 'targetAttribute' => ['advisor_id' => 'id']],
            [['archived_invoice_id'], 'exist', 'skipOnError' => true, 'targetClass' => ArchivedInvoice::className(), 'targetAttribute' => ['archived_invoice_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'archived_invoice_id' => 'Archived Invoice ID',
            'attributed_euc' => 'Attributed Euc',
            'advisor_id' => 'Advisor ID',
            'commission_euc' => 'Commission Euc',
            'n_operations_c' => 'N Operations C',
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
    public function getArchivedInvoice()
    {
        return $this->hasOne(ArchivedInvoice::className(), ['id' => 'archived_invoice_id']);
    }
}
