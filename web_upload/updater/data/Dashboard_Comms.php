<?php
try {
    $GLOBALS['db']->run("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('config.home.comms', '1');");
    return true;
} catch (\PDOException $e) {
    return false;
}
