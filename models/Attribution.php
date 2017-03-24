<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

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
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Advisor $advisor
 * @property AttributionType $attributionType
 * @property Office $office0
 * @property Transaction $transaction
 */
class Attribution extends \yii\db\ActiveRecord
{
    public $amount_eu;
    private $_dbTransaction;
    private $_before_delete_tx_payroll;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'attribution';
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
            [['advisor_id', 'attribution_type_id', 'transaction_id'], 'required'],
            [['advisor_id', 'attribution_type_id', 'transaction_id'], 'integer'],
            [['comments'], 'string'],
            [['office'], 'string', 'max' => 18],
            [['advisor_id'], 'exist', 'skipOnError' => true, 'targetClass' => Advisor::className(), 'targetAttribute' => ['advisor_id' => 'id']],
            [['attribution_type_id'], 'exist', 'skipOnError' => true, 'targetClass' => AttributionType::className(), 'targetAttribute' => ['attribution_type_id' => 'id']],
            [['office'], 'exist', 'skipOnError' => true, 'targetClass' => Office::className(), 'targetAttribute' => ['office' => 'name']],
            [['transaction_id'], 'exist', 'skipOnError' => true, 'targetClass' => Transaction::className(), 'targetAttribute' => ['transaction_id' => 'id']],
            ['office', 'default', 'value' => null]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'advisor_id' => Yii::t('app', 'Advisor'),
            'office' => Yii::t('app', 'Office'),
            'attribution_type_id' => Yii::t('app', 'Attribution Type'),
            'amount_eu' => Yii::t('app', 'Attributed Amount'),
            'transaction_id' => Yii::t('app', 'Transaction ID'),
            'comments' => Yii::t('app', 'Comments'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactionPayroll()
    {
        return $this->hasOne(TransactionPayroll::className(), ['id' => 'transaction_payroll_id']);
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

    public function afterFind() {
        parent::afterFind();
        $this->amount_eu = round($this->amount_euc / 100., 2);
        
    }

    public function beforeSave($insert) {
        $this->_dbTransaction = Yii::$app->db->beginTransaction();
        if ($this->transaction->payrolled_at and !$this->transaction_payroll_id) try {
            $tx_payroll = TransactionPayroll::find()->joinWith('attributions')
                ->where(['advisor_id' => $this->advisor_id])
                ->andWhere(['transaction_id' => $this->transaction_id])
                ->one();
            if (!$tx_payroll) {
                $month = substr($this->transaction->payrolled_at, 0, 7);
                $year = substr($this->transaction->payrolled_at, 0, 4);
                $accumulated_attribution_euc = TransactionAttributionSummary::find()
                    ->select(['sum(total_attributed_sum_euc)'])
                    ->where(['advisor_id' => $this->advisor_id])
                    ->andWhere(['to_char(payrolled_at, \'yyyy\')' => $year])
                    ->andWhere(['<=', 'to_char(payrolled_at, \'yyyy-mm\')', $month])
                    ->createCommand()->queryColumn()[0];
                if (!$accumulated_attribution_euc) $accumulated_attribution_euc = 0;
                $tranche = AdvisorTranche::selectTranche($this->advisor->getTranches()->asArray()->all(),
                    $accumulated_attribution_euc);
                $tx_payroll = new TransactionPayroll([
                    'commission_bp' => $tranche['commission_bp'],
                    'accumulated_euc' => $accumulated_attribution_euc
                ]);
                if (!$tx_payroll->save()) {
                    $msg = var_export($tx_payroll->errors, 1);
                    throw new \Exception($msg);
                }
            }
            $this->transaction_payroll_id = $tx_payroll->id;
            $this->amount_euc = 0;
        } catch (\Exception $e) {
            $this->_dbTransaction->rollback();
            throw new \yii\web\HttpException(500, $e->getMessage());
        }
        return parent::beforeSave($insert);
    }
    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
        $this->_dbTransaction->commit();
    }
    public function beforeDelete() {
        $this->_dbTransaction = Yii::$app->db->beginTransaction();
        $this->_before_delete_tx_payroll = $this->transactionPayroll;
        return parent::beforeDelete();
    }
    public function afterDelete() {
        if ($this->_before_delete_tx_payroll and !$this->_before_delete_tx_payroll->attributions) {
            if ($this->_before_delete_tx_payroll->delete() === false) {
                $this->_dbTransaction->rollback();
                throw new \yii\web\HttpException(500, 'Could not delete transaction payroll');
            }
        }
        $this->_dbTransaction->commit();
    }
}
