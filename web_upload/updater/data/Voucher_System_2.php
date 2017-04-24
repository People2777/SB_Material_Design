<?php
try {
    $GLOBALS['db']->run("DELETE FROM `:prefix:vay4er`");
    $GLOBALS['db']->run("ALTER TABLE `:prefix:vay4er` ADD `servers` varchar(128) NOT NULL;");

    return true;
} catch (\PDOException $e) {
    return false;
}
