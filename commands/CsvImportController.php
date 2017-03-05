<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\validators\StringValidator;

use app\models\Configuration;
use app\models\Contact;
use app\models\ContactDump;
use app\models\PropertyDump;

use yii\helpers\StringHelper;
use yii\helpers\ArrayHelper;

use ruskid\csvimporter\CSVImporter;
use ruskid\csvimporter\CSVReader;
use ruskid\csvimporter\MultipleImportStrategy;

/**
 */
class CsvImportController extends Controller {

    /**
     */
    public function actionProperties() {
        $property_dump = new PropertyDump();
        $attributes = ArrayHelper::map(Configuration::find()->where([
            'category' => 'ONOFFICE_CSV2PROPERTY'
        ])->asArray()->all(), 'value', 'name');
        $configs = $this->mkConfig($property_dump, $attributes);

        $url = Configuration::find()->where([
            'category' => 'FTP_ONOFFICE',
            'name' => 'PROPERTIES_URL'
        ])->one()->value;
        $importer = new CSVImporter;
        $importer->setData(new CSVReader([
            'filename' => $url,
                'fgetcsvOptions' => [
                    'delimiter' => ';'
                ]
        ]));
        $connection = Yii::$app->getDb();
        $connection->createCommand()->truncateTable(PropertyDump::tableName())->execute();
        $nrows = $importer->import(new MultipleImportStrategy([
            'tableName' => PropertyDump::tableName(),
            'configs' => $configs
        ]));
        $created_at = date('Y-m-d H:i:s') . substr(explode(' ', microtime())[0], 1, 3);
        $rows = $connection->createCommand(
            "insert into property ( \n" .
            "    reference, \n" .
            "    entry_date, \n" .
            "    active_date, \n" .
            "    inactive_date, \n" .
            "    property_type, \n" .
            "    location, \n" .
            "    building_complex, \n" .
            "    geo_coordinates, \n" .
            "    plot_area_m2, \n" .
            "    built_area_m2, \n" .
            "    n_bedrooms, \n" .
            "    created_at, \n" .
            "    updated_at) \n" .
            "select \n" .
            "    pd.reference, \n" .
            "    pd.entry_date, \n" .
            "    pd.active_date, \n" .
            "    pd.inactive_date, \n" .
            "    pd.property_type, \n" .
            "    pd.location, \n" .
            "    pd.building_complex, \n" .
            "    pd.geo_coordinates, \n" .
            "    pd.plot_area_m2, \n" .
            "    pd.built_area_m2, \n" .
            "    pd.n_bedrooms, \n" .
            "    :arg1, \n" .
            "    :arg2 \n" .
            "from property_dump pd \n" .
            "    left join property p on (p.reference = pd.reference) \n" .
            "where p.id is null and \n" .
            "      pd.reference is not null", [
            ':arg1' => $created_at,
            ':arg2' => $created_at
        ])->execute();
        $updated_at = date('Y-m-d H:i:s') . substr(explode(' ', microtime())[0], 1, 3);
        $rows = $connection->createCommand(
            "update property set \n" .
            "    entry_date = pd.entry_date, \n" .
            "    active_date = pd.active_date, \n" .
            "    inactive_date = pd.inactive_date, \n" .
            "    property_type = pd.property_type, \n" .
            "    location = pd.location, \n" .
            "    building_complex = pd.building_complex, \n" .
            "    geo_coordinates = pd.geo_coordinates, \n" .
            "    plot_area_m2 = pd.plot_area_m2, \n" .
            "    built_area_m2 = pd.built_area_m2, \n" .
            "    n_bedrooms = pd.n_bedrooms, \n" .
            "    updated_at = :arg1 \n" .
            "from property_dump pd \n" .
            "where property.updated_at < :arg2 and \n" .
            "      pd.reference = property.reference", [
            ':arg2' => $created_at,
            ':arg1' => $updated_at
        ])->execute();
    }
    /**
     */
    public function actionContacts() {
        $contact_dump = new ContactDump();
        $attributes = ArrayHelper::map(Configuration::find()->where([
            'category' => 'ONOFFICE_CSV2CONTACT'
        ])->asArray()->all(), 'value', 'name');
        $configs = $this->mkConfig($contact_dump, $attributes);

        $url = Configuration::find()->where([
            'category' => 'FTP_ONOFFICE',
            'name' => 'CONTACTS_URL'
        ])->one()->value;
        $importer = new CSVImporter;
        $importer->setData(new CSVReader([
            'filename' => $url,
                'fgetcsvOptions' => [
                    'delimiter' => ';'
                ]
        ]));
        $connection = Yii::$app->getDb();
        $connection->createCommand()->truncateTable(ContactDump::tableName())->execute();
        $nrows = $importer->import(new MultipleImportStrategy([
            'tableName' => ContactDump::tableName(),
            'configs' => $configs
        ]));
        $created_at = date('Y-m-d H:i:s') . substr(explode(' ', microtime())[0], 1, 3);
        $rows = $connection->createCommand(
            "insert into contact ( \n" .
            "    reference, \n" .
            "    first_name, \n" .
            "    last_name, \n" .
            "    nationality, \n" .
            "    type_of_data, \n" .
            "    contact_source, \n" .
            "    internet, \n" .
            "    birth_date, \n" .
            "    country_of_residence, \n" .
            "    created_at, \n" .
            "    updated_at) \n" .
            "select \n" .
            "    cd.customer_number, \n" .
            "    cd.first_name, \n" .
            "    cd.last_name, \n" .
            "    cd.nationality, \n" .
            "    cd.type_of_data, \n" .
            "    cd.contact_source, \n" .
            "    cd.internet, \n" .
            "    cd.birth_date, \n" .
            "    cd.country_of_residence, \n" .
            "    :arg1, \n" .
            "    :arg2 \n" .
            "from contact_dump cd \n" .
            "    left join contact c on (c.reference = cd.customer_number) \n" .
            "where c.id is null and \n" .
            "      cd.customer_number is not null", [
            ':arg1' => $created_at,
            ':arg2' => $created_at
        ])->execute();
        $updated_at = date('Y-m-d H:i:s') . substr(explode(' ', microtime())[0], 1, 3);
        $rows = $connection->createCommand(
            "update contact set \n" .
            "    first_name = cd.first_name, \n" .
            "    last_name = cd.last_name, \n" .
            "    nationality = cd.nationality, \n" .
            "    type_of_data = cd.type_of_data, \n" .
            "    contact_source = cd.contact_source, \n" .
            "    internet = cd.internet, \n" .
            "    birth_date = cd.birth_date, \n" .
            "    country_of_residence = cd.country_of_residence, \n" .
            "    updated_at = :arg1 \n" .
            "from contact_dump cd \n" .
            "where contact.updated_at < :arg2 and \n" .
            "      cd.customer_number = contact.reference", [
            ':arg2' => $created_at,
            ':arg1' => $updated_at
        ])->execute();

        return 0;
    }
    /**
     */
    private function mkConfig($model, $attributes) {
        $configs = [];
        foreach ($model->getValidators() as $validator)
            if ($validator instanceof StringValidator) {
                foreach ($validator->attributes as $attr) {
                    $i = array_search($attr, $attributes);
                    if ($i === false) continue;
                    $max = $validator->max;
                    $configs[] = [
                        'attribute' => $attr,
                        'value' => function($line) use ($i, $max) {
                            if (empty($line[$i]))
                                return null;
                            else return StringHelper::truncate($line[$i], $max, null);
                        },
                        'unique' => $attributes[$i] == 'customer_number'
                    ];
                }
            }
        return $configs;
    }
}
