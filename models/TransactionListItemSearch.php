<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use app\models\TransactionListItem;

/**
 * TransactionListItemSearch represents the model behind the search form about `app\models\TransactionListItem`.
 */
class TransactionListItemSearch extends TransactionListItem
{
    public $option_signed_from;
    public $option_signed_to;
    public $first_invoiced_from;
    public $first_invoiced_to;
    public $search_any;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['transaction_type', 'advisors', 'option_signed_from', 'option_signed_to', 'search_any', 'first_invoiced_from', 'first_invoiced_to'], 'safe'],
            [['approved', 'payrolled', 'invoiced', 'with_collaborator'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(), [
            'option_signed_from' => Yii::t('app', 'Option Signed From'),
            'option_signed_to' => Yii::t('app', 'Option Signed To'),
            'first_invoiced_from' => Yii::t('app', 'First Invoiced From'),
            'first_invoiced_to' => Yii::t('app', 'First Invoiced To'),
            'search_any' => Yii::t('app', 'Search Any'),
            'approved' => Yii::t('app', 'Approved'),
            'payrolled' => Yii::t('app', 'Payrolled'),
            'with_collaborator' => Yii::t('app', 'Collaborator'),
            'invoiced' => Yii::t('app', 'Invoiced'),
            'advisors' => Yii::t('app', 'Advisor')
        ]);
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = TransactionListItem::find();

        // add conditions that should always apply here
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->setSort([
            'defaultOrder' => ['option_signed_at' => SORT_DESC]
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        if ($this->option_signed_from and !$this->option_signed_to)
            $this->option_signed_to = date('Y-m-d');
        if (!$this->option_signed_from and $this->option_signed_to)
            $this->option_signed_from = '1970-01-01';

        if ($this->first_invoiced_from and !$this->first_invoiced_to)
            $this->first_invoiced_to = date('Y-m-d');
        if (!$this->first_invoiced_from and $this->first_invoiced_to)
            $this->first_invoiced_from = '1970-01-01';

        $query->andFilterWhere(
            ['between', 'option_signed_at', $this->option_signed_from, $this->option_signed_to]);
        $query->andFilterWhere(
            ['between', 'first_invoiced_at', $this->first_invoiced_from, $this->first_invoiced_to]);

        $query->andFilterWhere([
            'transaction_type' => $this->transaction_type,
            'advisors' => $this->advisors,
            'approved' => $this->approved,
            'payrolled' => $this->payrolled,
            'invoiced' => $this->invoiced,
            'with_collaborator' => $this->with_collaborator
        ]);
        $query->andFilterWhere(['or', 
            ['id' => $this->search_any],
            ['ilike', 'transaction_type', $this->search_any],
            ['ilike', 'custom_type', $this->search_any],
            ['ilike', 'transfer_type', $this->search_any],
            ['ilike', 'development_type', $this->search_any],
            ['ilike', 'buyer_provider', $this->search_any],
            ['ilike', 'seller_provider', $this->search_any],
            ['ilike', 'lead_type', $this->search_any],
            ['ilike', 'comments', $this->search_any],
            ['ilike', 'property_location', $this->search_any],
            ['ilike', 'property_building_complex', $this->search_any],
            ['ilike', 'property_reference', $this->search_any],
            ['ilike', 'seller_reference', $this->search_any],
            ['ilike', 'seller_name', $this->search_any],
            ['ilike', 'buyer_reference', $this->search_any],
            ['ilike', 'buyer_name', $this->search_any],
            ['ilike', 'advisors', $this->search_any]]);

        return $dataProvider;
    }
}
