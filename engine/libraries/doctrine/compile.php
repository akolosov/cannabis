#!/usr/bin/env php
<?php
include_once('Doctrine.php');

spl_autoload_register(array('Doctrine', 'autoload'));

print "Compile Doctrine.PgSQL.compiled.php...\n";

Doctrine_Compiler::compile('Doctrine.PgSQL.compiled.php', array('pgsql'));

print "All Done!\n\n";
?>