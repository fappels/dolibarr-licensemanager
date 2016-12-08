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

CREATE TABLE `llx_license_product` (
  `rowid` INT NOT NULL AUTO_INCREMENT,
  `tms` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `fk_product` INT NOT NULL,
  `fk_base_key` INT NOT NULL,
  `option_code` VARCHAR(50) NULL DEFAULT NULL,
  `fk_user_author` INT NULL DEFAULT NULL,
  `import_key` VARCHAR(14) NULL DEFAULT NULL,
  PRIMARY KEY (`rowid`))
ENGINE = InnoDB