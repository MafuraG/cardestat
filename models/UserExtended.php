<?php

namespace app\models;

class UserExtended extends User {
    public static function primaryKey() {
        return ['id'];
    }
}
