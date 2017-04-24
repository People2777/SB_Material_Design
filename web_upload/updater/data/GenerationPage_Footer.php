<?php
try {
    $GLOBALS['db']->run("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('page.footer.allow_show_data', '0')");
    return true;
} catch (\PDOException $e) {
    return false;
}
