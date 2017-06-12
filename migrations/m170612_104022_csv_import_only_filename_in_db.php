<?php

use yii\db\Migration;

class m170612_104022_csv_import_only_filename_in_db extends Migration
{
    public function safeUp()
    {
        $this->delete('configuration', ['category' => 'FTP_ONOFFICE', 'name' => 'PROPERTIES_URL']);
        $this->delete('configuration', ['category' => 'FTP_ONOFFICE', 'name' => 'CONTACTS_URL']);
        $this->batchInsert('configuration', ['category', 'name', 'value'], [
            ['FTP_ONOFFICE', 'PROPERTIES_FILENAME', 'Properties.csv'],
            ['FTP_ONOFFICE', 'CONTACTS_FILENAME', 'Addresses FTP EXPORT.csv'],
        ]);

    }

    public function down()
    {
        return true;
    }

}
