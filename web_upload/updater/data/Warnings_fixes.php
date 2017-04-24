<?php
try {
    /* Фикс системы предупреждений */
    $table_struct = $GLOBALS['db']->getAll("DESCRIBE `:prefix:warns`");
    foreach ($table_struct as &$field) {
        if ($field['Field'] == "id") {
            if ($field['Key'] != "PRI") {
                $GLOBALS['db']->run("ALTER TABLE `:prefix:warns`
                                            ADD UNIQUE KEY `id` (`id`);");
            }

            if ($field['Extra'] != "auto_increment") {
                $GLOBALS['db']->run("ALTER TABLE `:prefix:warns`
                                            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;");
            }

            break;
        }
    }

    /* Для новой серверной части, чтобы ошибки не спамила. В 1.1.6 надо сделать... */
    $GLOBALS['db']->run("ALTER TABLE `:prefix:srvgroups`
                                ADD `maxbantime` INT NOT NULL DEFAULT '-1' AFTER `groups_immune`,
                                ADD `maxmutetime` INT NOT NULL DEFAULT '-1' AFTER `maxbantime`;");

    /* Обновление иконки МОДа TF2 */
    if ($GLOBALS['db']->getOne("SELECT `icon` FROM `:prefix:mods` WHERE `modfolder` = 'tf';") == "tf2.gif") {
        $GLOBALS['db']->run("UPDATE `:prefix:mods`
                                    SET `icon` = 'tf2.png'
                                    WHERE `modfolder` = 'tf';");
    }

    /* Удаление неиспользуемого контента в /images/games/ */
    $data = scandir(SB_ICONS);
    foreach ($data as &$obj) {
        if (!is_file(sprintf("%s/%s", SB_ICONS, $obj)))
            continue;

        if ((int) $GLOBALS['db']->getOne(sprintf("SELECT COUNT(`icon`) FROM `:prefix:mods` WHERE `icon` = %s;", $GLOBALS['db']->qstr($obj))) == 0) {
            @unlink(sprintf("%s/%s", SB_ICONS, $obj));
        }
    }

    return true;
} catch (\PDOException $e) {
    return false;
}
