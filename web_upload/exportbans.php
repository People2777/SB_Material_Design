<?php

include_once("init.php");

$exportpublic = (isset($GLOBALS['config']['config.exportpublic']) && $GLOBALS['config']['config.exportpublic'] == "1");

if(!$userbank->HasAccess(ADMIN_OWNER) && !$exportpublic)
    die("У Вас нет доступа к данной функции.");

if(!isset($_GET['type']))
    die("Не передан тип банов. Используйте только ссылки внутри веб-панели!");

$type   = (($_GET['type'] == "steam") ? "0" : (($_GET['type'] == "ip") ? "1" : die("Некорретный тип банов.")));
$cmd    = ($type == "0" ? "banid" : "addip");
$field  = ($type == "0" ? "authid" : "ip");

header('Content-Type: application/x-httpd-php php');
header('Content-Disposition: attachment; filename="banned_' . (($type == "0") ? "user" : "ip") . '.cfg"');

$bans = $GLOBALS['db']->getAll("SELECT authid FROM `:prefix:bans` WHERE length = '0' AND RemoveType IS NULL AND type = ?", array($type));
foreach ($bans as $ban) {
    print($cmd . " 0 " . $ban[$field] . "\r\n");
}
