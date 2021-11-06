<?php


class DB {

    public static function getConnection() {
        $params = require(ROOT.'config/db.php');
        return new PDO("mysql:host={$params['host']};dbname={$params['dbname']}", $params['user'], $params['password']);
    }
}