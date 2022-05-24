ALTER TABLE `chargement` ADD `publication2` BOOLEAN NULL DEFAULT NULL AFTER `publication`; 

UPDATE chargement set publication2=1 where publication='O'; 

UPDATE chargement set publication2=0 where publication='N'; 

 ALTER TABLE `chargement` DROP `publication`;

 ALTER TABLE `chargement` CHANGE `publication2` `publication` TINYINT(1) NOT NULL DEFAULT '0'; 

 ALTER TABLE `photos` ADD `releve_papier2` BOOLEAN NOT NULL DEFAULT '0' COMMENT 'Existe-t\'il un relevé papier ?' AFTER `releve_td`, ADD `releve_base2` BOOLEAN NOT NULL DEFAULT '0' COMMENT 'Existe-t\'il un relevé en base ?' AFTER `releve_papier2`, ADD `releve_td2` BOOLEAN NOT NULL DEFAULT '0' COMMENT 'Existe-t\'il un relevé de table décennale ?' AFTER `releve_base2`; 

UPDATE `photos` set `releve_papier2`=1 where `releve_papier`='O';

UPDATE `photos` set `releve_base2`=1 where `releve_base`='O';

UPDATE `photos` set `releve_td2`=1 where `releve_td`='O';

ALTER TABLE `photos` DROP `releve_papier`;
ALTER TABLE `photos` DROP `releve_base`;
ALTER TABLE `photos` DROP `releve_td`;

ALTER TABLE `photos` CHANGE `releve_papier2` `releve_papier` TINYINT(1) NOT NULL DEFAULT '0';
ALTER TABLE `photos` CHANGE `releve_base2` `releve_base` TINYINT(1) NOT NULL DEFAULT '0'; 
ALTER TABLE `photos` CHANGE `releve_td2` `releve_td` TINYINT(1) NOT NULL DEFAULT '0'; 