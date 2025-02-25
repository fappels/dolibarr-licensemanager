<?php
/* Copyright (C) 2014      Francis Appels        <francis.appels@z-application.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *   	\file       htdocs/licensemanager/class/licensemanager.php
 *		\ingroup    licensemanager
 *		\brief      licensemanager setup page, to setup private keys and enter or import key lists
 *					Initialy built by build_class_from_table on 2013-12-28 16:25
 */

//if (! defined('NOREQUIREUSER'))  define('NOREQUIREUSER','1');
//if (! defined('NOREQUIREDB'))    define('NOREQUIREDB','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN'))  define('NOREQUIRETRAN','1');
//if (! defined('NOCSRFCHECK'))    define('NOCSRFCHECK','1');			// Do not check anti CSRF attack test
//if (! defined('NOSTYLECHECK'))   define('NOSTYLECHECK','1');			// Do not check style html tag into posted data
//if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1');		// Do not check anti POST attack test
//if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');			// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');			// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))  define('NOREQUIREAJAX','1');
//if (! defined("NOLOGIN"))        define("NOLOGIN",'1');				// If this page is public (can be called outside logged session)

// Change this following line to use the correct relative path (../, ../../, etc)
$res = 0;
if (! $res && file_exists("../main.inc.php")) $res = @include '../main.inc.php';
if (! $res && file_exists("../../main.inc.php")) $res = @include '../../main.inc.php';
if (! $res && file_exists("../../../main.inc.php")) $res = @include '../../../main.inc.php';

if (! $res) die("Include of main fails");
// Change this following line to use the correct relative path from htdocs
dol_include_once('/licensemanager/class/licensekeylist.class.php');
dol_include_once('/licensemanager/class/licenselist.class.php');

// Load traductions files requiredby by page
$langs->load("admin");
$langs->load("licensemanager@licensemanager");

// Get parameters
$id			= GETPOST('id', 'int');
$action		= GETPOST('action', 'alpha');
$add	= GETPOST('add', 'alpha');
$clean	= GETPOST('clean', 'alpha');
$remove	= GETPOST('remove', 'alpha');

// Security check
if (! $user->admin) accessforbidden();
// tab setup and selection
$privateKeys = new stdClass;
$externalKeys = new stdClass;
$privateKeys->mode = 'privatekeys';
$privateKeys->title = $langs->trans('PrivateKeys') ? $langs->trans('PrivateKeys') : 'Private Keys';
$externalKeys->mode = 'externalkeys';
$externalKeys->title = $langs->trans('ExternalKeys') ? $langs->trans('ExternalKeys') : 'External Keys';
$mode = GETPOST('mode', 'alpha') ? GETPOST('mode', 'alpha') : $privateKeys->mode;

/*******************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 ********************************************************************/

$licenseKeylist = new Licensekeylist($db);
$licenseList = new Licenselist($db);
$res = $licenseKeylist->fetchList('', 'rowid ASC');
if ($res < 0) $error++;
$res = $licenseList->fetchList('', 'rowid ASC');
if ($res < 0) $error++;


if ($action == 'add-clean') {
	if ($add) {
		$licenseKeylist->type = GETPOST('type', 'int');
		$licenseKeylist->algo = GETPOST('algo', 'int');
		$licenseKeylist->option_code = GETPOST('option_code', 'alpha');
		$licenseKeylist->base_key = GETPOST('base_key', 'alpha');
		$licenseKeylist->name = GETPOST('name', 'alpha');
		$licenseKeylist->mode = GETPOST('mode', 'int');
		$licenseKeylist->multi_key_separator = GETPOST('multi_key_separator', 'alpha');
		$licenseKeylist->output_mode = GETPOST('output_mode', 'int');
		$licenseKeylist->duration = GETPOST('duration', 'int');
		$licenseKeylist->duration_unit = GETPOST('duration_unit', 'int');
		$res = $licenseKeylist->create($user);
	} elseif ($clean) {
		$res = $licenseKeylist->clean($user);
	}
	if ($res < 0) {
		$error++;
	} else {
		// Creation OK
		$licenseKeylist->fetchList('', 'rowid ASC');
	}
} elseif ($action == "external" && empty($refresh)) {
	$i = 0;
	if ($remove) {
		if (! empty($licenseList->dataset)) {
			foreach ($licenseList->dataset as $data) {
				$licenseList->id = $data['rowid'];

				$param = 'REMOVE_' . $data['rowid'] . $i;

				if (GETPOST($param, 'alpha')) {
					//delete
					$res = $licenseList->delete($user);
				}
				$i++;
				if ($res < 0) $error++;
			}
		}
	} elseif ($add) {
		$licenseList->fk_base_key = GETPOST('fk_base_key', 'int');
		$licenseList->external_key = GETPOST('external_key', 'alpha');
		$licenseList->locked = 0;
		$res = $licenseList->create($user);
	}


	$licenseList->fetchList('', 'rowid ASC');
}

if ($action && !$refresh && !(($action == 'selectall') || ($action == 'selectnone'))) {
	if (! $res > 0) $error++;

	if (! $error) {
		$db->commit();
		$mesg = '<font class="ok">' . $langs->trans("SetupSaved") . '</font>';
	} else {
		$db->rollback();
		$mesg = '<font class="error">' . $langs->trans("Error") . ' ' . $action . '</font>';
	}
}


/***************************************************
 * VIEW
 *
 * Put here all code to build page
 ****************************************************/

// init headers en tabs
$title = $langs->trans('LicenseManagerSetup');
$tabsTitle = $langs->trans('LicenseManager');
$tabs = array('tab1' => $privateKeys, 'tab2' => $externalKeys);
$head = licensemanager_admin_prepare_head($tabs);

llxHeader('', $title);
$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
print load_fiche_titre($title, $linkback, 'setup');
$form = new Form($db);
if ($mode == $tabs['tab1']->mode) {
	//tab1
	print dol_get_fiche_head($head, 'tab1', $tabsTitle, 0);
	$types = $licenseKeylist->getKeyTypes();
	$outputModes = $licenseKeylist->getOutputModes();
	print '<form action="' . $_SERVER['PHP_SELF'] . '?mode=privatekeys" method="POST">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="add-clean">';
	$var = true;
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans("LicenseType") . '</td>';
	print '<td>' . $langs->trans("algo") . '</td>';
	print '<td>' . $langs->trans("OptionCode") . '</td>';
	print '<td>' . $langs->trans("base_key") . '</td>';
	print '<td>' . $langs->trans("LicenseName") . '</td>';
	print '<td>' . $langs->trans("mode") . '</td>';
	print '<td>' . $langs->trans("multi_key_separator") . '</td>';
	print '<td>' . $langs->trans("output_mode");
	print '<td>' . $langs->trans("duration") . '</td>';
	print '<td></td>';
	print '</tr>' . "\n";
	if (! empty($licenseKeylist->dataset)) {
		$i = 0;
		foreach ($licenseKeylist->dataset as $data) {
			$var = !$var;
			print '<tr ' . $bc[$var] . '>';
			print '<td>' . $types[$data['type']] . '</td>';
			print '<td>' . $data['algo'] . '</td>';
			print '<td>' . $data['option_code'] . '</td>';
			print '<td>' . str_repeat('*', strlen($data['base_key'])) . '</td>';
			print '<td>' . $data['name'] . '</td>';
			print '<td>' . $data['mode'] . '</td>';
			print '<td>' . $data['multi_key_separator'] . '</td>';
			print '<td>' . $outputModes[$data['output_mode']] . '</td>';
			$duration_units = $licenseKeylist->getDurationUnits(($data['mode'] > 1) ? 1 : 0);
			print '<td>' . $data['duration'] . ' ' . $duration_units[$data['duration_unit']] . '</td>';
			print '<td></td></tr>';
			$i++;
		}
	}
	print '<tr><td>' . $form->selectarray('type', $licenseKeylist->getKeyTypes(), $licenseKeylist->type, 0, 0, 0) . '</td>';
	print '<td>' . $form->selectarray('algo', $licenseKeylist->getAlgos(), $licenseKeylist->algo, 0, 0, 1) . '</td>';
	print '<td><input name="option_code" size="5"></td>';
	print '<td><input name="base_key" size="16"></td>';
	print '<td><input name="name" size="16"></td>';
	print '<td>' . $form->selectarray('mode', $licenseKeylist->getModes(), $licenseKeylist->mode, 0, 0, 1) . '</td>';
	print '<td><input name="multi_key_separator" size="5"></td>';
	print '<td>' . $form->selectarray('output_mode', $licenseKeylist->getOutputModes(), $licenseKeylist->output_mode, 0, 0, 0) . '</td>';
	print '<td><input name="duration" size="5">&nbsp' . $form->selectarray('duration_unit', $licenseKeylist->getDurationUnits(), $licenseKeylist->duration_unit) . '</td>';
	print '<td><center>';
	print '<input type="submit" name="add" class="button" value="' . $langs->trans("add") . '">';
	print "</center></td></tr></table>";
	print '<center><input type="submit" name="clean" class="button" value="' . $langs->trans("clean") . '"></center>';
	print "</form>\n";
} elseif ($mode == $tabs['tab2']->mode) {
	//tab2
	print dol_get_fiche_head($head, 'tab2', $tabsTitle, 0);
	$listLicenseKeyList = new Licensekeylist($db);
	$licenseKeys = array();
	if ($listLicenseKeyList->fetchList('t.type = 1') > 0) {
		foreach ($listLicenseKeyList->dataset as $data) {
			$licenseKeys[$data['rowid']] = $data['name'];
		}
	}


	print '<form action="' . $_SERVER['PHP_SELF'] . '?mode=externalkeys" method="POST">';
	print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
	print '<input type="hidden" name="action" value="external">';
	$var = true;
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans("key") . '</td>';
	print '<td>' . $langs->trans("ExternalKey") . '</td>';
	print '<td><center><a href="' . $_SERVER['PHP_SELF'] . '?mode=externalkeys&action=selectall">' . $langs->trans("removeAll") . '</a>/<a href="' . $_SERVER['PHP_SELF'] . '?mode=externalkeys&action=selectnone">' . $langs->trans("None") . '</a></center></td>';
	print '</tr>' . "\n";
	if (! empty($licenseList->dataset)) {
		$i = 0;

		foreach ($licenseList->dataset as $data) {
			$var = !$var;
			print '<tr ' . $bc[$var] . '>';
			print '<td>' . $licenseKeys[$data['fk_base_key']] . '</td>';
			print '<td>' . $data['external_key'] . '</td>';
			if ($data['locked'] > 0) {
				print '<td align="center" width="40">';
				print '<input ' . $bc[$var] . ' type="checkbox" name="remove" value="0" disabled="disabled">';
				print '</td></tr>';
			} else {
				print '<td align="center" width="40">';
				$key = 'REMOVE_' . $data['rowid'] . $i;
				print '<input ' . $bc[$var] . ' type="checkbox" name="' . $key . '" value="1"' . ((($action == 'selectall') && $action != "selectnone") ? ' checked="checked"' : '') . '>';
				print '</td></tr>';
			}
			$i++;
		}
	}
	print '<tr><td>' . $form->selectarray('fk_base_key', $licenseKeys, GETPOST('fk_base_key', 'int'), 0, 0, 0) . '</td>';
	print '<td><input name="external_key" size="50"></td>';
	print '<td><center>';
	print '<input type="submit" name="add" class="button" value="' . $langs->trans("add") . '">';
	print "</center></td></tr></table>";
	print '<center><input type="submit" name="remove" class="button" value="' . $langs->trans("remove") . '"></center>';
	print "</form>\n";
}

dol_htmloutput_mesg($mesg);

// End of page
llxFooter();
$db->close();

/**
 *  Return array head with list of tabs to view object informations.
 *
 *  @param	Array	$tabs		tab names
 *  @return	array   	        head array with tabs
 */
function licensemanager_admin_prepare_head($tabs)
{
	$h = 0;
	$head = array();

	foreach ($tabs as $key => $value) {
		$head[$h][0] = dol_buildpath("/licensemanager/admin/licensemanager.php?mode=" . $value->mode, 1);
		$head[$h][1] = $value->title;
		$head[$h][2] = $key;
		$h++;
	}

	return $head;
}
