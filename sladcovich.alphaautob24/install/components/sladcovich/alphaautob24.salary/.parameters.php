<?php

defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

$arComponentParameters = [
    'PARAMETERS' => [
        'MOSCOW_MANAGERS_PERCENT' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_PARAMETER_MOSCOW_MANAGERS_PERCENT'),
            'TYPE' => 'NUMBER',
            'DEFAULT' => "",
        ),
        'REGION_MANAGERS_PERCENT' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_PARAMETER_REGION_MANAGERS_PERCENT'),
            'TYPE' => 'NUMBER',
            'DEFAULT' => "",
        ),
        'PARTS_PERCENT' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_PARAMETER_PARTS_PERCENT'),
            'TYPE' => 'NUMBER',
            'DEFAULT' => "",
        ),
        'EXPERTS_PERCENT' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_PARAMETER_EXPERTS_PERCENT'),
            'TYPE' => 'NUMBER',
            'DEFAULT' => "",
        ),
        'WORKERS_PERCENT' => array(
            'PARENT' => 'BASE',
            'NAME' => GetMessage('SLADCOVICH_ALPHAAUTOB24_SALARY_PARAMETER_WORKERS_PERCENT'),
            'TYPE' => 'NUMBER',
            'DEFAULT' => "",
        )
    ]
];