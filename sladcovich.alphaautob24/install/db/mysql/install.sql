-- Caution! Order of actions is important!
-- We need to create a dependent table first, only then the other table with binding fields

-- вкладка - Работы
CREATE TABLE IF NOT EXISTS `b_sladcovich_alphaautob24_entity_orm_work`
(
    `ID` int PRIMARY KEY AUTO_INCREMENT NOT NULL,

    `NAME` varchar(255) DEFAULT NULL COMMENT 'Наименование',
    `PRICE` decimal(12,2) DEFAULT NULL COMMENT 'Цена',
    `NH` decimal(12,2) DEFAULT NULL COMMENT 'Нормочас',
    `COUNT` decimal(12,2) DEFAULT NULL COMMENT 'Количество',
    `SUM` decimal(12,2) DEFAULT NULL COMMENT 'Сумма',

    `DEAL_B24_ID` int unsigned NOT NULL COMMENT 'ID сделки (Битрикс 24)',

    CONSTRAINT `work_deal_b24_id_FK`
        FOREIGN KEY (`DEAL_B24_ID`) REFERENCES `b_crm_deal` (`ID`)
) COMMENT='Работы';

-- дополнительная сущность для вкладки - работы
CREATE TABLE IF NOT EXISTS `b_sladcovich_alphaautob24_entity_orm_executor`
(
    `ID` int PRIMARY KEY AUTO_INCREMENT NOT NULL,

    `PARTICIPATION_PERCENT` decimal(12,2) DEFAULT NULL COMMENT '% участия',

    `USER_B24_ID` int NOT NULL COMMENT 'ID исполнителя (Битрикс 24)',
    `WORK_ID` int NOT NULL COMMENT 'ID работы',

    CONSTRAINT `executor_user_id_b24_FK`
        FOREIGN KEY (`USER_B24_ID`) REFERENCES `b_user` (`ID`),
    CONSTRAINT `executor_work_id_FK`
        FOREIGN KEY (`WORK_ID`) REFERENCES `b_sladcovich_alphaautob24_entity_orm_work` (`ID`)
) COMMENT='Участие исполнителей в работах';

-- вкладка - Работы СК
CREATE TABLE IF NOT EXISTS `b_sladcovich_alphaautob24_entity_orm_work_sk`
(
    `ID` int PRIMARY KEY AUTO_INCREMENT NOT NULL,

    `NAME` varchar(255) DEFAULT NULL COMMENT 'Наименование',
    `PRICE` decimal(12,2) DEFAULT NULL COMMENT 'Цена',
    `NH` decimal(12,2) DEFAULT NULL COMMENT 'Нормочас',
    `COUNT` decimal(12,2) DEFAULT NULL COMMENT 'Количество',
    `SUM` decimal(12,2) DEFAULT NULL COMMENT 'Сумма',

    `DEAL_B24_ID` int unsigned NOT NULL COMMENT 'ID сделки (Битрикс 24)',

    CONSTRAINT `work_sk_deal_b24_id_FK`
        FOREIGN KEY (`DEAL_B24_ID`) REFERENCES `b_crm_deal` (`ID`)
) COMMENT='Работы СК';

-- вкладка - Запчасти
CREATE TABLE IF NOT EXISTS `b_sladcovich_alphaautob24_entity_orm_part`
(
    `ID` int PRIMARY KEY AUTO_INCREMENT NOT NULL,

    `CATEGORY_NUMBER` varchar(255) DEFAULT NULL COMMENT 'Кат. №',
    `NAME` varchar(255) DEFAULT NULL COMMENT 'Наименование',
    `PRICE` decimal(12,2) DEFAULT NULL COMMENT 'Цена',
    `COEFFICIENT` decimal(12,2) DEFAULT NULL COMMENT 'Коэффициент',
    `COUNT` decimal(12,2) DEFAULT NULL COMMENT 'Количество',
    `SUM` decimal(12,2) DEFAULT NULL COMMENT 'Сумма',

    `DEAL_B24_ID` int unsigned NOT NULL COMMENT 'ID сделки (Битрикс 24)',

    CONSTRAINT `part_deal_b24_id_FK`
        FOREIGN KEY (`DEAL_B24_ID`) REFERENCES `b_crm_deal` (`ID`)
) COMMENT='Запчасти';

-- вкладка - Запчасти СК
CREATE TABLE IF NOT EXISTS `b_sladcovich_alphaautob24_entity_orm_part_sk`
(
    `ID` int PRIMARY KEY AUTO_INCREMENT NOT NULL,

    `CATEGORY_NUMBER` varchar(255) DEFAULT NULL COMMENT 'Кат. №',
    `NAME` varchar(255) DEFAULT NULL COMMENT 'Наименование',
    `PRICE` decimal(12,2) DEFAULT NULL COMMENT 'Цена',
    `COEFFICIENT` decimal(12,2) DEFAULT NULL COMMENT 'Коэффициент',
    `COUNT` decimal(12,2) DEFAULT NULL COMMENT 'Количество',
    `SUM` decimal(12,2) DEFAULT NULL COMMENT 'Сумма',

    `DEAL_B24_ID` int unsigned NOT NULL COMMENT 'ID сделки (Битрикс 24)',

    CONSTRAINT `part_sk_deal_b24_id_FK`
        FOREIGN KEY (`DEAL_B24_ID`) REFERENCES `b_crm_deal` (`ID`)
) COMMENT='Запчасти СК';

-- вкладка - Себестоимость
CREATE TABLE IF NOT EXISTS `b_sladcovich_alphaautob24_entity_orm_cost_price`
(
    `ID` int PRIMARY KEY AUTO_INCREMENT NOT NULL,

    `PP_NUMBER` varchar(255) DEFAULT NULL COMMENT 'Номер п/п',
    `PP_DATE` date DEFAULT NULL COMMENT 'Дата п/п',
    `SUM` decimal(12,2) DEFAULT NULL COMMENT 'Сумма',
    `NOTE` varchar(255) DEFAULT NULL COMMENT 'Примечание',

    `DEAL_B24_ID` int unsigned NOT NULL COMMENT 'ID сделки (Битрикс 24)',

    CONSTRAINT `cost_price_deal_b24_id_FK`
        FOREIGN KEY (`DEAL_B24_ID`) REFERENCES `b_crm_deal` (`ID`)
) COMMENT='Себестоимость';
