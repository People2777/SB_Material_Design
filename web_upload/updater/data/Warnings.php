<?php
try {
    $GLOBALS['db']->run("CREATE TABLE `:prefix:warns` (
    `id` int(11) NOT NULL,
    `arecipient` int(11) NOT NULL,
    `afrom` int(11) NOT NULL,
    `expires` int(11) NOT NULL,
    `reason` varchar(256) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

    $GLOBALS['db']->run("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('admin.warns', '1'), ('admin.warns.max', '3');");
} catch (\PDOException $e) {
    return false;
}
