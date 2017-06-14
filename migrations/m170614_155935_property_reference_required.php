<?php

use yii\db\Migration;

class m170614_155935_property_reference_required extends Migration
{
    public function up()
    {
        $this->execute('alter table property alter reference set not null');
    }

    public function down()
    {
        return true;
    }
}
