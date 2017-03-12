<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "property".
 *
 * @property integer $id
 * @property string $reference
 * @property string $entry_date
 * @property string $active_date
 * @property string $inactive_date
 * @property string $property_type
 * @property string $location
 * @property string $building_complex
 * @property string $geo_coordinates
 * @property string $plot_area_m2
 * @property string $built_area_m2
 * @property string $n_bedrooms
 * @property string $created_at
 * @property string $updated_at
 *
 * @property Transaction[] $transactions
 */
class Property extends \yii\db\ActiveRecord
{
    public $plot_area_m2;
    public $built_area_m2;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'property';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'required'],
            [['created_at', 'updated_at'], 'safe'],
            [['reference', 'active_date', 'inactive_date'], 'string', 'max' => 12],
            [['entry_date', 'property_type', 'building_complex'], 'string', 'max' => 24],
            [['location'], 'string', 'max' => 48],
            [['geo_coordinates'], 'string', 'max' => 32],
            [['plot_area_m2', 'built_area_m2'], 'string', 'max' => 8],
            [['n_bedrooms'], 'string', 'max' => 4],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('app', 'ID'),
            'reference' => Yii::t('app', 'Reference'),
            'entry_date' => Yii::t('app', 'Entry Date'),
            'active_date' => Yii::t('app', 'Active Date'),
            'inactive_date' => Yii::t('app', 'Inactive Date'),
            'property_type' => Yii::t('app', 'Property Type'),
            'location' => Yii::t('app', 'Location'),
            'building_complex' => Yii::t('app', 'Building/Complex'),
            'geo_coordinates' => Yii::t('app', 'Geo Coordinates'),
            'plot_area_m2' => Yii::t('app', 'Plot Area'),
            'built_area_m2' => Yii::t('app', 'Built Area'),
            'n_bedrooms' => Yii::t('app', 'No. Bedrooms'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getTransactions()
    {
        return $this->hasMany(Transaction::className(), ['property_id' => 'id']);
    }

    public function afterFind() {
        parent::afterFind();
        $this->plot_area_m2 = round($this->plot_area_dm2 / 100., 2);
        $this->built_area_m2 = round($this->built_area_dm2 / 100., 2);
    }
}
