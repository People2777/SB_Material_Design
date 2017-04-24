<?php
try {
    $GLOBALS['db']->run("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('config.changeadmininfos', '1');");
    return true;
} catch (\PDOException $e) {
    return false;
}
