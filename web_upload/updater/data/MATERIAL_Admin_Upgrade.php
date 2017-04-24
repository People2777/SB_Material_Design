<?php
try {
    $_need_expired = true;
    $_struct = $GLOBALS['db']->getAll("DESCRIBE `:prefix:admins`");
    foreach ($_struct as $_obj) {
        if ($_obj['Field'] == "expired") {
            $_need_expired = false;
            break;
        }
    }

    if ($_need_expired) {
        $GLOBALS['db']->run("ALTER TABLE `:prefix:admins` 
            ADD `expired` 	int(11) NULL;");
    }

    $GLOBALS['db']->run("ALTER TABLE `:prefix:admins` 
            ADD `skype`		varchar(128) 	NULL,
            ADD `comment`	varchar(128)	 NULL,
            ADD `vk`		varchar(128) 	NULL,
            ADD `support`	int(6) 			NULL DEFAULT '0';");


	$GLOBALS['db']->run("INSERT INTO `:prefix:settings` (`setting`, `value`) VALUES
			('config.dateformat_ver2', 'd.m.Y'),
			('config.text_home', 'Добро пожаловать на сайт игрового портала: AZAZA'),
			('config.text_mon', 'У вас есть возможность управлять игроками через мониторинг(test)'),

			('config.text_acc', 'Успешный вход в систему!'),
			('config.text_acc2', 'Подробно ознакомьтесь с данными на это странице!'),

			('template.global', '0'),
			('dash.info_block',	'1'),
			('dash.info_block_text',	'<h1>Медовое сообщество</h1><br><center><img src=\"themes/new_box/img/pchelka.jpg\" class=\"img-responsive\" alt=\"\"></center>Дополнительная инфа о нас. ТЕСТ. текстик: Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.<br />Дополнительная инфа о нас. ТЕСТ. текстик: Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.<br />Дополнительная инфа о нас. ТЕСТ. текстик: Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer posuere erat a ante.<br /><b>Все указывается в настройках!</b>'),
			('dash.info_vk',	'http://vk.com/'),
			('dash.info_steam',	'http://steam.com/'),
			('dash.info_yout',	'http://youtube.com'),
			('dash.info_face',	'http://facebock.com/'),
			('dash.info_block_text_t',	'С уважением, главная администрация.'),
			('page.adminlist',	'0'),
			('page.xleb',	'1'),
			('theme.style', 'lightblue'),
			('theme.style.color', '');");

    $qs = array("UPDATE `:prefix:settings` SET `value` = '<center><p>SB Material Design успешно установлена/обновлена!</p><p>Добро пожаловать :)</p></center>' WHERE `setting` = 'dash.intro.text';",
            "UPDATE `:prefix:settings` SET `value` = 'images/logos/sb-dark.png' WHERE `setting` = 'template.logo';",
            "UPDATE `:prefix:settings` SET `value` = 'SourceBans :: MATERIAL' WHERE `setting` = 'template.title';",
            "UPDATE `:prefix:settings` SET `value` = '0' WHERE `setting` = 'config.enableprotest';",
            "UPDATE `:prefix:settings` SET `value` = '0' WHERE `setting` = 'config.enablesubmit';",
            "UPDATE `:prefix:settings` SET `value` = 'd.m.Y в H:i' WHERE `setting` = 'config.dateformat';",
            "UPDATE `:prefix:settings` SET `value` = 'new_box' WHERE `setting` = 'config.theme';",
            "UPDATE `:prefix:admins` SET `expired` = 0");
    foreach ($qs as &$query) {
        $GLOBALS['db']->run($query);
    }

    return true;
} catch (\PDOException $e) {
    return false;
}
