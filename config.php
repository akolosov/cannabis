<?php

// $Id$

ini_set("output_buffering",	1);

// Использовать полную версию Doctrine
define('USE_FULL_DOCTRINE', false);

// Корневые подразделение, группа пользователей и пользователь
define('ROOT_DIVISION', 0);
define('ROOT_GROUP', 0);
define('SYSTEM_USER', -1);
define('ADMIN_USER', 1);

// Пути
define('ABSOLUTE_PATH', "/var/www/cannabis");
define('HANDLERS_PATH', "engine/handlers");
define('MODULES_PATH', "engine/modules");
define('CLASSES_PATH', "engine/classes");
define('TEMPLATES_PATH', MODULES_PATH);
define('COMMON_MODULES_PATH', MODULES_PATH."/common");
define('LIBRARY_PATH', "engine/libraries");
define('TOOL_PATH', "engine/tools");
define('JAVASCRIPT_PATH', "engine/javascripts");
define('MODELS_PATH', "engine/models");
define('DATA_PATH', "data");
define('LOG_PATH', DATA_PATH."/logs");
define('CACHE_PATH', DATA_PATH."/cache");
define('CONFIG_PATH', DATA_PATH."/configs");
define('FILE_CACHE_PATH', DATA_PATH."/files");

// Имя и путь файла журнала
define('LOG_FILE_NAME', LOG_PATH."/".strftime("%d.%m.%Y", time()));

// Временная зона
define('TIME_ZONE', "Asia/Yekaterinburg");

// Записывать ошибки
define('LOG_ERRORS', ADVANCED_DEBUG_MODE);

// Записывать отладочную информацию
define('LOG_DEBUGS', DEBUG_MODE);

// Записывать исключения
define('LOG_EXCEPTIONS', true);

// Показывать исключения
define('DISPLAY_EXCEPTIONS', true);

// Показывать ошибки
define('DISPLAY_ERRORS', true);

// Отсылать отчет об ошибках
define('EMAIL_ERRORS', true);
// ... на адрес
define('EMAIL_FOR_ERRORS', 'cannabis@uk-most.ru');

// При передаче на следующее действие ставить дату и время старта сразу и запускать его код
define('EXECUTE_IMMEDIATELY', false);

// Использовать PHP5 XDebug для вывода информации об ошибках
define('USE_XDEBUG', false);

// Charset по умолчанию
define('DEFAULT_CHARSET', 'UTF-8');

// Limit для списка процессов по умолчанию
define('DEFAULT_LIMIT', 100);

// Время между действиями, до которого хронология не учитывается (секунд) 
define('CHRONOLOGY_TIMEOUT', 1);

// Время между стартом действия и паузой при уже старотовавшем действии, но так и оставшимся таковым
define('PAUSE_TIMEOUT', 5);

// Максимальный размер файлов, разрешенных для загрузки
define('MAX_FILE_SIZE', 20971520);

// Количество событий в строке таблицы при рендеринге
define('EVENTS_IN_ROW', 2);

// Префикс для конфигурационных параметров
define('CONFIG_PREFIX', 'CFG_');

// База данных
define('DATABASE_DSN', "pgsql://postgres:bumpy@localhost:5432/cannabis");

// Установка временной зоны
date_default_timezone_set(TIME_ZONE);

?>
