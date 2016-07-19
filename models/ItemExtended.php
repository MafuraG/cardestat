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
    public static function findLeaves($root_id) {
        $t = self::tableName();
        return static::find()
            ->innerJoin('item_extended root', "$t.path like replace(root.path, E'\\\\', E'\\\\\\\\') || '%'")
            ->leftJoin('item child', "child.parent_id = $t.id")
            ->where(['child.id' => null])
            ->andWhere(["root.id" => $root_id])
            ->distinct();
    }
}
