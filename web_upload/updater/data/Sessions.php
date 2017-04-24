<?php
$GLOBALS['db']->run("CREATE TABLE IF NOT EXISTS `:prefix:session` (
  `session_id` varchar(64) NOT NULL,
  `data` text NOT NULL,
  `last_usage` int(11) NOT NULL,
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;");
