<?php
try {
    $GLOBALS['db']->Execute("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('theme.bg', '');");
    $GLOBALS['db']->Execute("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('theme.bg.rep', '');");
    $GLOBALS['db']->Execute("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('theme.bg.att', '');");
    $GLOBALS['db']->Execute("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('theme.bg.pos', '');");

    return true;
} catch (\PDOException $e) {
    return false;
}
