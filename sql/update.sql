-- Copyright (C) 2025 Francis Appels <francis.appels@z-application.com>
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see http://www.gnu.org/licenses/.

ALTER TABLE llx_license_order ADD COLUMN date_creation date NULL;
ALTER TABLE llx_license_order ADD COLUMN date_valid date NULL;
ALTER TABLE llx_license_order ADD COLUMN status integer NOT NULL DEFAULT 0;
UPDATE llx_license_order los set date_creation = (select MAX(lod.datec) FROM llx_license_orderdet lod WHERE lod.fk_license_order = los.rowid);
UPDATE llx_license_order los set date_valid = (select MAX(lod.datev) from llx_license_orderdet lod WHERE lod.fk_license_order = los.rowid);
