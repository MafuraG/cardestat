<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "property_dump".
 *
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
 */
class PropertyDump extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'property_dump';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
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
            'reference' => Yii::t('app', 'Reference'),
            'entry_date' => Yii::t('app', 'Entry Date'),
            'active_date' => Yii::t('app', 'Active Date'),
            'inactive_date' => Yii::t('app', 'Inactive Date'),
            'property_type' => Yii::t('app', 'Property Type'),
            'location' => Yii::t('app', 'Location'),
            'building_complex' => Yii::t('app', 'Building Complex'),
            'geo_coordinates' => Yii::t('app', 'Geo Coordinates'),
            'plot_area_m2' => Yii::t('app', 'Plot Area M2'),
            'built_area_m2' => Yii::t('app', 'Built Area M2'),
            'n_bedrooms' => Yii::t('app', 'N Bedrooms'),
        ];
    }
}
