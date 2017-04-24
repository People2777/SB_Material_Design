<?php
try {
    if ($GLOBALS['db']->getOne("SELECT count(*) FROM `:prefix:settings` WHERE setting = 'theme.style'")) == 0)
        $GLOBALS['db']->run("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('theme.style', 'lightblue')");

    if ($GLOBALS['db']->getOne("SELECT count(*) FROM `:prefix:settings` WHERE setting = 'theme.style.color'"))
        $GLOBALS['db']->run("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES ('theme.style.color', '')");

    if ($GLOBALS['db']->getOne("SELECT count(*) FROM `:prefix:settings` WHERE setting = 'dash.intro.title'"))
        $GLOBALS['db']->run("DELETE FROM `:prefix:settings` WHERE `setting` = 'dash.intro.title'");

    if ($GLOBALS['db']->getOne("SELECT count(*) FROM `:prefix:settings` WHERE setting = 'template.title'"))
        $GLOBALS['db']->run("UPDATE `:prefix:settings` SET `setting` = 'template.title', `value` = 'SourceBans :: MATERIAL' WHERE `setting` = 'template.title'");

    return true;
} catch (\PDOException $e) {
    return false;
}
