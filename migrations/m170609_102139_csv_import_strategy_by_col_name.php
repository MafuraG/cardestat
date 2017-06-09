<?php

use yii\db\Migration;

class m170609_102139_csv_import_strategy_by_col_name extends Migration
{
    public function safeUp() {
        $this->delete('configuration', ['category' => 'ONOFFICE_CSV2CONTACT']);
        $this->delete('configuration', ['category' => 'ONOFFICE_CSV2PROPERTY']);
        $this->execute('alter table contact_dump alter customer_number drop not null');
        $this->batchInsert('configuration', ['category', 'name', 'value'], [
            ['ONOFFICE_CSV2PROPERTY', 'reference', 'Reference'],
            ['ONOFFICE_CSV2PROPERTY', 'entry_date', 'Entry Date'],
            ['ONOFFICE_CSV2PROPERTY', 'active_date', 'Active Date'],
            ['ONOFFICE_CSV2PROPERTY', 'inactive_date', 'Inactive Date'],
            ['ONOFFICE_CSV2PROPERTY', 'units', 'Units for sale of this type (start)'],
            ['ONOFFICE_CSV2PROPERTY', 'property_type', 'Property type'],
            ['ONOFFICE_CSV2PROPERTY', 'location', 'Location'],
            ['ONOFFICE_CSV2PROPERTY', 'building_complex', 'Building/Complex'],
            ['ONOFFICE_CSV2PROPERTY', 'geo_coordinates', 'GE - Coordinates'],
            ['ONOFFICE_CSV2PROPERTY', 'plot_area_m2', 'Plot area'],
            ['ONOFFICE_CSV2PROPERTY', 'built_area_m2', 'Built area'],
            ['ONOFFICE_CSV2PROPERTY', 'n_bedrooms', 'Bedrooms'],
            ['ONOFFICE_CSV2CONTACT', 'first_name', '**Vorname'],
            ['ONOFFICE_CSV2CONTACT', 'last_name', '**Name / Firma'],
            ['ONOFFICE_CSV2CONTACT', 'nationality', 'Country'],
            ['ONOFFICE_CSV2CONTACT', 'type_of_data', '**Type of Data'],
            ['ONOFFICE_CSV2CONTACT', 'contact_source', '**Contact source'],
            ['ONOFFICE_CSV2CONTACT', 'internet', '**Internet'],
            ['ONOFFICE_CSV2CONTACT', 'birth_date', '1 - Fecha nacim.'],
            ['ONOFFICE_CSV2CONTACT', 'customer_number', 'CustNr'],
            ['ONOFFICE_CSV2CONTACT', 'country_of_residence', 'País de resid.']]);
    }

    public function down()
    {
        return true;
    }
}
