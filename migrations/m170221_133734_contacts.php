<?php

use yii\db\Migration;

class m170221_133734_contacts extends Migration
{
    public function safeUp() {
        $this->createTable('contact_dump', [
            'customer_number' => $this->string(20)->unique(),
            'first_name' => $this->string(42),
            'last_name' => $this->string(42),
            'nationality' => $this->string(42),
            'type_of_data' => $this->string(42),
            'contact_source' => $this->string(42),
            'internet' => $this->string(42),
            'birth_date' => $this->string(20),
            'country_of_residence' => $this->string(42)
        ]);
        $this->createTable('contact', [
            'id' => $this->primaryKey(),
            'reference' => $this->string(20)->notNull()->unique(),
            'first_name' => $this->string(42),
            'last_name' => $this->string(42),
            'nationality' => $this->string(42),
            'type_of_data' => $this->string(42),
            'contact_source' => $this->string(42),
            'internet' => $this->string(42),
            'birth_date' => $this->string(20),
            'country_of_residence' => $this->string(42),
            'created_at' => $this->timestamp(2)->notNull(),
            'updated_at' => $this->timestamp(2)->notNull()
        ]);
        $this->createTable('property_dump', [
            'reference' => $this->string(12),
            'entry_date' => $this->string(24),
            'active_date' => $this->string(12),
            'inactive_date' => $this->string(12),
            'property_type' => $this->string(24),
            'location' => $this->string(48),
            'building_complex' => $this->string(24),
            'geo_coordinates' => $this->string(32),
            'plot_area_m2' => $this->string(8),
            'built_area_m2' => $this->string(8),
            'n_bedrooms' => $this->string(4)
        ]);
        $this->createTable('property', [
            'id' => $this->primaryKey(),
            'reference' => $this->string(12),
            'entry_date' => $this->string(24),
            'active_date' => $this->string(12),
            'inactive_date' => $this->string(12),
            'property_type' => $this->string(24),
            'location' => $this->string(48),
            'building_complex' => $this->string(24),
            'geo_coordinates' => $this->string(32),
            'plot_area_m2' => $this->string(8),
            'built_area_m2' => $this->string(8),
            'n_bedrooms' => $this->string(4),
            'created_at' => $this->timestamp(2)->notNull(),
            'updated_at' => $this->timestamp(2)->notNull()
        ]);
        $this->batchInsert('configuration', ['category', 'name', 'value'], [[
            'FTP_ONOFFICE',
            'user',
            '',
        ], [
            'FTP_ONOFFICE',
            'password',
            '',
        ], [
            'FTP_ONOFFICE',
            'PROPERTIES_URL',
            'file:///home/claudio/projects/cardestat/Properties.csv',
        ], [
            'FTP_ONOFFICE',
            'CONTACTS_URL',
            'file:///home/claudio/projects/cardestat/Addresses FTP EXPORT.csv',
        ], [
            'ONOFFICE_CSV2PROPERTY',
            'reference',
            '2',
        ], [
            'ONOFFICE_CSV2PROPERTY',
            'entry_date',
            '4',
        ], [
            'ONOFFICE_CSV2PROPERTY',
            'active_date',
            '7',
        ], [
            'ONOFFICE_CSV2PROPERTY',
            'inactive_date',
            '8',
        ], [
            'ONOFFICE_CSV2PROPERTY',
            'property_type',
            '32',
        ], [
            'ONOFFICE_CSV2PROPERTY',
            'location',
            '40',
        ], [
            'ONOFFICE_CSV2PROPERTY',
            'building_complex',
            '41',
        ], [
            'ONOFFICE_CSV2PROPERTY',
            'geo_coordinates',
            '45',
        ], [
            'ONOFFICE_CSV2PROPERTY',
            'plot_area_m2',
            '91',
        ], [
            'ONOFFICE_CSV2PROPERTY',
            '92',
            'built_area_m2',
        ], [
            'ONOFFICE_CSV2PROPERTY',
            'n_bedrooms',
            '97',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'first_name',
            '8',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'last_name',
            '9',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'nationality',
            '10',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'type_of_data',
            '34',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'contact_source',
            '35',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'internet',
            '36',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'birth_date',
            '57',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'customer_number',
            '5',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'country_of_residence',
            '56',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'telephone_1',
            '91',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'telephone_2',
            '92',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'telephone_3',
            '93',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'telephone_4',
            '94',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'telephone_5',
            '95',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'telephone_6',
            '96',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'telephone_7',
            '97',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'telephone_8',
            '98',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'telephone_9',
            '99',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'telephone_10',
            '100',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'email_1',
            '111',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'email_2',
            '112',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'email_3',
            '113',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'email_4',
            '114',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'email_5',
            '115',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'email_6',
            '116',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'email_7',
            '117',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'email_8',
            '118',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'email_9',
            '119',
        ], [
            'ONOFFICE_CSV2CONTACT',
            'email_10',
            '120',
        ]]);
    }
    public function safeDown() {
        $this->delete('configuration', ['category' => 'ONOFFICE_CSV2CONTACT']);
        $this->delete('configuration', ['category' => 'FTP_ONOFFICE']);
        $this->dropTable('property');
        $this->dropTable('property_dump');
        $this->dropTable('contact');
        $this->dropTable('contact_dump');
    }
}
