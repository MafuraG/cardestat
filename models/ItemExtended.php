<?php

namespace app\models;

use app\models\Item;

class ItemExtended extends Item {
    public static function primaryKey() {
        return ['id'];
    }
    public function getChildren() {
        return $this->hasMany(ItemExtended::className(), ['parent_id' => 'id']);
    }
}
