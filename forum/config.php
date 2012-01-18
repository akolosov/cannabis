<?php

$db_type = 'pgsql';
$db_host = 'localhost';
$db_name = 'cannabis';
$db_username = 'postgres';
$db_password = 'bumpy';
$db_prefix = 'pbb_';
$p_connect = false;

$cookie_name = 'punbb_cookie';
$cookie_domain = '';
$cookie_path = '/';
$cookie_secure = 0;
$cookie_seed = 'c9ea8818';

$dbconnect = pg_connect("host=$db_host dbname=$db_name user=$db_username password=$db_password");

define('PUN', 1);