<?php 
try {
    $find_su = false;
    $find_oa = false;
    $find_og = false;

    $mods_struct = $GLOBALS['db']->getAll("DESCRIBE `:prefix:mods");
    $tables = $GLOBALS['db']->getAll("SHOW TABLES;");

    /* Check */
    foreach ($tables as $table) { // TABLES
        if (strpos($table[0], "srvgroups_overrides") !== FALSE)
            $find_og = true;
        else if (strpos($table[0], "overrides") !== FALSE)
            $find_oa = true;
    }

    foreach ($mods_struct as $obj) {
        if ($obj['Field'] == "steam_universe") {
            $find_su = true;
            break;
        }
    }

    /* Process requests */
    if (!$find_su) {
        $GLOBALS['db']->run("ALTER TABLE `:prefix:mods` ADD `steam_universe` int(11)");
        $GLOBALS['db']->run("ALTER TABLE `:prefix:mods` `steam_universe` SET DEFAULT 0;");
    }

    if (!$find_oa) {
        $GLOBALS['db']->run("CREATE TABLE IF NOT EXISTS `:prefix:overrides` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `type` enum('command','group') NOT NULL,
                                    `name` varchar(32) NOT NULL,
                                    `flags` varchar(30) NOT NULL,
                                    PRIMARY KEY (`id`),
                                    UNIQUE KEY `type` (`type`,`name`)
                                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    if (!$find_og) {
        $GLOBALS['db']->run("CREATE TABLE IF NOT EXISTS `:prefix:srvgroups_overrides` (
                                    `id` int(11) NOT NULL AUTO_INCREMENT,
                                    `group_id` smallint(5) unsigned NOT NULL,
                                    `type` enum('command','group') NOT NULL,
                                    `name` varchar(32) NOT NULL,
                                    `access` enum('allow','deny') NOT NULL,
                                    PRIMARY KEY (`id`),
                                    UNIQUE KEY `group_id` (`group_id`,`type`,`name`)
                                ) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
    }

    $GLOBALS['db']->run("DROP TABLE IF EXISTS `:prefix:net_country`, `:prefox:net_country_ip`");
} catch (\PDOException $e) {
    return false;
}
