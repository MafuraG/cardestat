<?php

namespace app\models;

use yii\db\ActiveRecord;

class ItemReadingGroupExtended extends ItemReadingGroup {
    public static function primaryKey() {
        return ['id'];
    }
}
