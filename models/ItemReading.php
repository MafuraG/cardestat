<?php

namespace app\models;

use yii\db\ActiveRecord;

class ItemReading extends ActiveRecord {
    public function getItemExtended() {
        return $this->hasOne(ItemExtended::className(), ['id' => 'item_id']);
    }
    public function getItemGroup() {
        return $this->hasOne(ItemGroup::className(), ['id' => 'item_group_id']);
    }
}
