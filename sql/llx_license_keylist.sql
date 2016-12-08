-- ============================================================================
-- 
-- Copyright (C) 2013      Francis Appels        <francis.appels@z-application.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ============================================================================


CREATE TABLE `llx_license_keylist`  (
  `rowid` INT NOT NULL AUTO_INCREMENT,
  `tms` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `type` TINYINT NULL DEFAULT 0 COMMENT '0 = private key 1 = listed key',
  `algo` VARCHAR(50) NOT NULL,
  `option_code` VARCHAR(50) NULL DEFAULT NULL,
  `base_key` VARCHAR(255) NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `mode` VARCHAR(45) NULL DEFAULT 'single',
  `multi_key_separator` VARCHAR(16) DEFAULT '-',
  `output_mode` VARCHAR(45) NULL DEFAULT 'text',
  `duration` FLOAT NULL,
  `duration_unit` VARCHAR(1) NULL,
  PRIMARY KEY (`rowid`))
ENGINE = InnoDB