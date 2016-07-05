<?php

namespace app\models;

use yii\db\ActiveRecord;

class ItemReadingGroup extends ActiveRecord {
    public function getItemReadingsExtended() {
        return $this->hasMany(ItemReadingExtended::className(), ['item_reading_group_id' => 'id'])
            ->orderBy(['path' => SORT_ASC]);
    }
}
