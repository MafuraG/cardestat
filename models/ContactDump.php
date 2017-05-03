<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_dump".
 *
 * @property string $customer_number
 * @property string $first_name
 * @property string $last_name
 * @property string $nationality
 * @property string $type_of_data
 * @property string $contact_source
 * @property string $internet
 * @property string $birth_date
 * @property string $country_of_residence
 */
class ContactDump extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contact_dump';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['customer_number', 'birth_date'], 'string', 'max' => 20],
            [['first_name', 'last_name', 'nationality', 'contact_source', 'internet', 'country_of_residence'], 'string', 'max' => 42],
            ['type_of_data', 'string', 'max' => 54],
            [['customer_number'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'customer_number' => Yii::t('app', 'Customer Number'),
            'first_name' => Yii::t('app', 'First Name'),
            'last_name' => Yii::t('app', 'Last Name'),
            'nationality' => Yii::t('app', 'Nationality'),
            'type_of_data' => Yii::t('app', 'Type Of Data'),
            'contact_source' => Yii::t('app', 'Contact Source'),
            'internet' => Yii::t('app', 'Internet'),
            'birth_date' => Yii::t('app', 'Birth Date'),
            'country_of_residence' => Yii::t('app', 'Country Of Residence'),
        ];
    }
}
