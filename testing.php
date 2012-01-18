<?php

// $Id$

define(ENGINE_NAME,			"cannabis");
define(ENGINE_DESCR,		"Objectives, Resources and Processes System"); // Цели, Ресурсы и Система Процессов
define(ENGINE_DESCR_SHORT,	"ORP System");
define(ENGINE_VERSION,		"1.3.0.0");
define(ENGINE_BUILD,		"sativa");
define(DEBUG_MODE,			false);
define(RUNTIME_DEBUG_MODE,	DEBUG_MODE);
define(ADVANCED_DEBUG_MODE,	false);

# кофигурация системы
require_once("config.php");
setlocale(LC_ALL, "ru_RU");
mb_internal_encoding(DEFAULT_CHARSET);

# дополнительные параметры и общие функции
require_once(COMMON_MODULES_PATH."/options.php");
require_once(HANDLERS_PATH."/commonFunctions.php");

# библиотеки Doctrine ORM
if (USE_FULL_DOCTRINE) {
	require_once(LIBRARY_PATH."/doctrine/Doctrine.php");
} else {
	require_once(LIBRARY_PATH."/doctrine/Doctrine.PgSQL.compiled.php");
}

# обработчики запросов, отладчик и пр.
require_once(HANDLERS_PATH."/errorsHandler.php");
require_once(HANDLERS_PATH."/debugsHandler.php");
require_once(HANDLERS_PATH."/sessionsHandler.php");

# дополнительная кофигурация системы
require_once("override.config.php");
# дополнительные параметры системы
require_once(COMMON_MODULES_PATH."/override.options.php");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
  <meta http-equiv="pragma" content="no-cache">
  <meta http-equiv="cache-control" content="no-cache">
  <meta http-equiv="content-type" content="text/html; charset=<?= DEFAULT_CHARSET; ?>">
<?php foreach ($css_files as $css_file => $css_media): ?>
  <link rel="stylesheet" href="<?= $css_file; ?>" type="text/css" media="<?= $css_media; ?>" />
<?php endforeach; ?>
<?php foreach ($js_files as $js_file): ?>
  <script type="text/javascript" src="<?= $js_file; ?>"></script>
<?php endforeach; ?>
</head>
<body>
<?php
//$manager	= new CalendarManager($engine, USER_CODE);
//$calendar	= $manager->createCalendar(array('owner' => $engine, 'name' => 'testing', 'description' => 'testing 1 2 3', 'is_public' => true));
//$permission	= $calendar->createPermission(array('account_id' => 96, 'permission_id' => 3));
//$event		= $calendar->createEvent(array('subject' => 'test', 'event' => 'test 1 2 3', 'started_at' => strftime("%Y-%m-%d %H:%M:%S", time()), 'ended_at' => strftime("%Y-%m-%d %H:%M:%S", time()+3600), 'priority_id' => Constants::EVENT_PRIORITY_NORMAL));
//$calendar->save();

//$manager		= new ContactListManager($engine, USER_CODE);
//$contactlist	= $manager->createContactList(array('owner' => $manager, 'name' => 'testing', 'description' => 'testing 1 2 3', 'is_public' => true));
//$permission		= $contactlist->createPermission(array('account_id' => 96, 'permission_id' => 3));
//$account		= $contactlist->createAccount(array('account_id' => 96));
//$account		= $contactlist->createAccount(array('account_id' => 95));
//$account		= $contactlist->createAccount(array('account_id' => 94));
//$account		= $contactlist->createAccount(array('account_id' => 93));
//$account		= $contactlist->createAccount(array('account_id' => 92));
//$account		= $contactlist->createAccount(array('account_id' => 91));
//$account		= $contactlist->createAccount(array('account_id' => 90));
//$contactlist->save();
//print $contactlist->getAccountProperty(96, 'email')."<br />";
//print $contactlist->getAccountProperty(95, 'email')."<br />";
//print $contactlist->getAccountProperty(94, 'email')."<br />";
//
//$manager		= new FileManager($engine, USER_CODE);
//$folder			= $manager->createFile(array('owner' => $manager, 'name' => 'test.folder', 'description' => 'testing 1 2 3', 'is_folder' => true));
//$permission		= $folder->addPermission($folder->createPermission(array('account_id' => 96, 'permission_id' => 3)));
//$permission		= $folder->addPermission($folder->createPermission(array('account_id' => 95, 'permission_id' => 3)));
//$permission		= $folder->addPermission($folder->createPermission(array('account_id' => 94, 'permission_id' => 3)));
//$permission		= $folder->addPermission($folder->createPermission(array('account_id' => 93, 'permission_id' => 3)));
//$permission		= $folder->addPermission($folder->createPermission(array('account_id' => 92, 'permission_id' => 3)));
//$folder->save();
//$filecontent	= base64_encode(file_get_contents('data/files/test.dat'));
//$file			= $manager->createFile(array('owner' => $manager, 'name' => 'test.dat', 'description' => 'testing 1 2 3', 'blob' => $filecontent, 'parent_id' => $folder->getProperty('id')));
//$file->copyPermissionsFrom($folder);
//$file->save();
//if ($manager->isDeleted(1)) {
//	$manager->undeleteFile(1);
//} else {
//	$manager->deleteFile(1);
//}
//$manager->saveFile(1);
//$manager->eraseFile(1);
//
//$manager		= new MessageManager($engine, USER_CODE);
//$message		= $manager->createMessage(array('owner' => $manager, 'subject' => 'testing', 'text' => 'testing 1 2 3'));
//$filecontent	= base64_encode(file_get_contents('data/files/test.dat'));
//$message->addBlob(MessageBlob::create(array('owner' => $message, 'name' => 'test.dat', 'blob' => $filecontent)));
//$message->addBlob(MessageBlob::create(array('owner' => $message, 'name' => 'testing.dat', 'blob' => $filecontent)));
//$message->addReciever(MessageReciever::create(array('owner' => $message, 'reciever_id' => 95)));
//$message->addReciever(MessageReciever::create(array('owner' => $message, 'reciever_id' => 93)));
//$message->save();
//

$cacheDriver = new Doctrine_Cache_Memcache(array('servers' => array('host' => 'localhost', 'port' => 11211, 'persistent' => true), 'compression' => false));

$connection->setAttribute(Doctrine::ATTR_QUERY_CACHE, $cacheDriver);
$connection->setAttribute(Doctrine::ATTR_RESULT_CACHE, $cacheDriver);
$connection->setAttribute(Doctrine::ATTR_QUERY_CACHE_LIFESPAN, 60);
$connection->setAttribute(Doctrine::ATTR_RESULT_CACHE_LIFESPAN, 60);

$st = getMicroTime();
$users = $connection->execute('select * from accounts_tree')->fetchAll();
print "#1 - ".getProcessTime($st).'s <br />';

$st = getMicroTime();
$users = $connection->execute('select * from accounts_tree')->fetchAll();
print "#2 - ".getProcessTime($st).'s <br />';

$st = getMicroTime();
$users = $connection->execute('select * from accounts_tree')->fetchAll();
print "#3 - ".getProcessTime($st).'s <br />';

print "<br /><br />"; 
printStatistics();

phpinfo();
?>
</body>
</html>
