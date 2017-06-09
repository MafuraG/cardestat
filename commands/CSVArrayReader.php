<?php

namespace app\commands;

use ruskid\csvimporter\CSVReader;

class CSVArrayReader extends CSVReader {
    public $data = [];
    public $filename = 'void';
    public function readFile() {
        return $this->data;
    }
}
