<?php

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Config\Option;

Option::set('sladcovich.alphaautob24','MOSCOW_MANAGERS_PERCENT', 4);
Option::set('sladcovich.alphaautob24','REGION_MANAGERS_PERCENT', 5);
Option::set('sladcovich.alphaautob24','PARTS_PERCENT', 1);
Option::set('sladcovich.alphaautob24','EXPERTS_PERCENT', 1);
Option::set('sladcovich.alphaautob24','WORKERS_PERCENT', 100);
