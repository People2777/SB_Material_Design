<?php
try {
    $GLOBALS['db']->run("ALTER TABLE `:prefix:menu` ADD `newtab` INT(4) NOT NULL DEFAULT '0' AFTER `enabled`;");
    return true;
} catch (\PDOException $e) {
    return false;
}
