<?php
try {
    $GLOBALS['db']->run("CREATE TABLE IF NOT EXISTS `:prefix:vay4er` ( `aid` int(6) NOT NULL auto_increment, `activ` int(6) NOT NULL, `value` bigint(20) NOT NULL, `days` int(11) NOT NULL, `group_web` varchar(128) NOT NULL, `group_srv` varchar(128) NOT NULL, PRIMARY KEY  (`aid`)) ENGINE='MyISAM' DEFAULT CHARSET=utf8;");

    if ($GLOBALS['db']->getOne("SELECT count(*) FROM `:prefix:settings` WHERE setting = 'page.vay4er'"))
        $GLOBALS['db']->run("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('page.vay4er', '0')");

    return true;
} catch (\PDOException $e) {
    return false;
}
