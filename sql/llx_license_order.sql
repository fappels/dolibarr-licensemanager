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


CREATE TABLE `llx_license_order` (
  `rowid` INT NOT NULL AUTO_INCREMENT,
  `tms` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `note` VARCHAR(255) NULL DEFAULT NULL,
  `fk_user_author` INT NULL,
  `identification` VARCHAR(50) NULL DEFAULT NULL,
  `output_mode` VARCHAR(45) NULL,
  `key_mode` VARCHAR(45) NULL,
  `fk_customer` INT NOT NULL,
  `fk_commande` INT NOT NULL,
  `qty_seq_id` INT NOT NULL,
  PRIMARY KEY (`rowid`))
ENGINE = InnoDB
