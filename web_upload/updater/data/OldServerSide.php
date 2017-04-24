<?php
try {
    $GLOBALS['db']->run("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('feature.old_serverside', '1');");
} catch (\PDOException $e) {
    return false;
}
