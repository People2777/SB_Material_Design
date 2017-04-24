<?php
try {
    $GLOBALS['db']->run("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('theme.bg.size', '');");
} catch (\PDOException $e) {
    try {
        $GLOBALS['db']->run("UPDATE `:prefix:settings` SET `setting` = 'theme.bg.size', `value` = '' WHERE `setting` = 'theme.bg.size' AND `setting` = 'theme.bg.size' COLLATE utf8mb4_bin;");
    } catch (\PDOException $e) {
        return false;
    }
}

return true;
