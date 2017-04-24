<?php
try {
    $GLOBALS['db']->run("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('theme.splashscreen', '1'), ('theme.home.stats', '1');");
} catch (\PDOException $e) {
    return false;
}
