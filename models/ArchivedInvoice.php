<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "archived_invoice".
 *
 * @property integer $id
 * @property integer $amount_euc
 * @property string $office
 * @property string $transaction_type
 * @property string $subject
 * @property integer $n_operations_c
 * @property string $month
 *
 * @property ArchivedAttribution[] $archivedAttributions
 * @property ArchiveSubject $subject0
 * @property Office $office0
 * @property TransactionType $transactionType
 */
class ArchivedInvoice extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'archived_invoice';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['amount_euc', 'transaction_type', 'subject', 'n_operations_c'], 'required'],
            [['amount_euc', 'n_operations_c'], 'integer'],
            [['month'], 'safe'],
            [['office', 'transaction_type'], 'string', 'max' => 18],
            [['subject'], 'string', 'max' => 32],
            [['subject'], 'exist', 'skipOnError' => true, 'targetClass' => ArchiveSubject::className(), 'targetAttribute' => ['subject' => 'name']],
            [['office'], 'exist', 'skipOnError' => true, 'targetClass' => Office::className(), 'targetAttribute' => ['office' => 'name']],
            [['transaction_type'], 'exist', 'skipOnError' => true, 'targetClass' => TransactionType::className(), 'targetAttribute' => ['transaction_type' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'amount_euc' => 'Amount Euc',
            'office' => 'Office',
            'transaction_type' => 'Transaction Type',
            'subject' => 'Subject',
            'n_operations_c' => 'N Operations C',
            'month' => 'Month',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getArchivedAttributions()
    {
        return $this->hasMany(ArchivedAttribution::className(), ['archived_invoice_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSubject0()
    {
        return $this->hasOne(ArchiveSubject::className(), ['name' => 'subject']);
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
    public function getTransactionType()
    {
        return $this->hasOne(TransactionType::className(), ['name' => 'transaction_type']);
    }
}
