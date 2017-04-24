<?php
try {
    $GLOBALS['db']->run("ALTER TABLE `" . DB_PREFIX . "_avatars` DROP `expires`;");
