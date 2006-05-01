
CREATE TABLE IF NOT EXISTS `manager` ( `manager_id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY , `name` VARCHAR( 15 ) NOT NULL , `secret` VARCHAR( 50 ) , `deny` VARCHAR( 255 ) , `permit` VARCHAR( 255 ) , `read` VARCHAR( 50 ) , `write` VARCHAR( 50 ) );

