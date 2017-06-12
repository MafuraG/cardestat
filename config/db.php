<?php

//$dbopts = parse_url($_SERVER['DATABASE_URL']);
$dbopts = parse_url(getenv('DATABASE_URL'));

return [
    'class' => 'yii\db\Connection',
    'dsn' => 'pgsql:dbname='.ltrim($dbopts['path'],'/').';host='.$dbopts['host'].';port='.$dbopts['port'],
    'username' => $dbopts['user'],
    'password' => $dbopts['pass'],
    'charset' => 'utf8',
];
