<?php
try {
    $GLOBALS['db']->run("CREATE TABLE IF NOT EXISTS `:prefix:avatars` (`authid` varchar(35) NOT NULL, `url` varchar(150) NOT NULL, `expires` int(11) NOT NULL, UNIQUE KEY `authid` (`authid`)) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    return true;
} catch (\PDOException $e) {
    return false;
}
