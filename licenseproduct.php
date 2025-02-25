<?php
/* Copyright (C) 2014	   Francis Appels       <francis.appels@z-application.com>
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
 *   	\file       licensemanager/licenseproduct.php
 *		\ingroup    licensemanager product
 *		\brief      This file is used to link a private key to a product.
 *					Initialy built by build_class_from_table on 2013-12-28 16:27
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

require_once DOL_DOCUMENT_ROOT . '/core/class/canvas.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/product.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/company.lib.php';
// Change this following line to use the correct relative path from htdocs
dol_include_once('/licensemanager/class/licenseproduct.class.php');
dol_include_once('/licensemanager/class/licensekeylist.class.php');

// Load traductions files requiredby by page
$langs->load("products");
$langs->load("licensemanager@licensemanager");
$langs->load("other");
$langs->load("orders");

// Get parameters
$id	= GETPOST('id', 'int');
$ref = GETPOST('ref', 'alpha');
$type = GETPOST('type', 'int');
$action = (GETPOST('action', 'alpha') ? GETPOST('action', 'alpha') : 'view');
$confirm = GETPOST('confirm', 'alpha');
$cancel = GETPOST('cancel');
$fieldid = isset($_GET["ref"]) ? 'ref' : 'rowid';

// Protection if external user
if ($user->societe_id > 0) {
	accessforbidden();
}

// Security check
$fieldvalue = (! empty($id) ? $id : (! empty($ref) ? $ref : ''));
$fieldtype = (! empty($ref) ? 'ref' : 'rowid');
$result = restrictedArea($user, 'produit|service', $fieldvalue, 'product&product', '', '', $fieldtype, $objcanvas);

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('productcard'));

// create a license and product objects
$licenseProduct = new Licenseproduct($db);
$product = new Product($db);
$licenseKeylist = new Licensekeylist($db);

if ($ref) $result = $product->fetch('', $ref);
if ($id > 0) $result = $product->fetch($id);
if ($product->id > 0) $result = $licenseProduct->fetch(0, $product->id);

/*******************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 ********************************************************************/

if (($product->id > 0) && ($action == 'update_license') && ! GETPOSTISSET('cancel') && ($user->rights->produit->creer || $user->rights->service->creer)) {
	// link license to product
	$licenseProduct->fk_product = $product->id;
	$licenseProduct->fk_base_key = GETPOSTINT('fk_base_key');
	$licenseProduct->option_code = GETPOST('option_code', 'alpha');
	$licenseProduct->fk_user_author = $user->id;
	if ($licenseProduct->id == 0) {
		$result = $licenseProduct->create($user);
	} else {
		$result = $licenseProduct->update($user);
	}
}

if (($product->id > 0) && ($action == 'confirm_delete') && ! GETPOSTISSET('cancel') && ($user->rights->produit->creer || $user->rights->service->creer)) {
	$result = $licenseProduct->delete($user);
	$licenseProduct->id = 0;
}

if (($action != 'view') && ($action != 'edit_license') && ! GETPOSTISSET('cancel') && ! GETPOSTISSET('delete')) {
	if (! $result > 0) $error++;

	if (! $error) {
		$db->commit();
		$mesg = '<font class="ok">' . $langs->trans("Saved") . '</font>';
	} else {
		$db->rollback();
		$mesg = '<font class="error">' . $langs->trans("Error") . ' ' . $action . '</font>';
	}
}




/***************************************************
 * VIEW
 *
 ****************************************************/

if ($id > 0 || $ref) {
	//$help_url='EN:Module_Stocks_En|FR:Module_Stock|ES:M&oacute;dulo_Stocks';
	llxHeader("", $langs->trans("CardProduct" . $product->type), $help_url);

	$head = product_prepare_head($product, $user);
	$titre = $langs->trans("CardProduct" . $product->type);
	$picto = ($product->type == 1 ? 'service' : 'product');
	dol_fiche_head($head, 'licenseproduct', $titre, 0, $picto);

	$form = new Form($db);

	print '<table class="border" width="100%">';

	// Ref
	print '<tr>';
	print '<td width="30%">' . $langs->trans("Ref") . '</td><td>';
	print $form->showrefnav($product, 'ref', '', 1, 'ref');
	print '</td>';
	print '</tr>';

	// Label
	print '<tr><td>' . $langs->trans("Label") . '</td><td>' . $product->libelle . '</td>';
	print '</tr>';

	// Status (to sell)
	print '<tr><td>' . $langs->trans("Status") . ' (' . $langs->trans("Sell") . ')</td><td>';
	print $product->getLibStatut(2, 0);
	print '</td></tr>';

	// Status (to buy)
	print '<tr><td>' . $langs->trans("Status") . ' (' . $langs->trans("Buy") . ')</td><td>';
	print $product->getLibStatut(2, 1);
	print '</td></tr>';
	if ($licenseProduct->id > 0) {
		if ($licenseKeylist->fetch($licenseProduct->fk_base_key) > 0) {
			$keyTypes = $licenseKeylist->getKeyTypes();
			// LicenseType
			print '<tr><td>' . $langs->trans("LicenseType") . '</td><td>' . $keyTypes[$licenseKeylist->type] . '</td>';
			print '</tr>';
			// PrivateKey
			print '<tr><td>' . $langs->trans("PrivateKey") . '</td><td>' . $licenseKeylist->name . '</td>';
			print '</tr>';
			// OptionCode
			print '<tr><td>' . $langs->trans("OptionCode") . '</td><td>' . $licenseProduct->option_code . '</td>';
			print '</tr>';
		}
	} else {
		// no License
		print '<tr><td>' . $langs->trans("LicenseType") . '</td><td>' . $langs->trans('None') . '</td>';
		print '</tr>';
	}

	print "</table>";

	print '</div>';

	/* ************************************************************************** */
	/* action button                                                                      */
	/* ************************************************************************** */

	print "\n" . '<div class="tabsAction">' . "\n";

	if ($user->rights->produit->creer || $user->rights->service->creer) {
		print '<a class="butAction" href="' . $_SERVER['PHP_SELF'] . '?action=edit_license&amp;id=' . $product->id . '">' . $langs->trans("EditLicense") . '</a>';
	}

	print "\n</div>\n";

	/*
	 * Edit license
	 */
	if ($action == 'edit_license' && ($user->rights->produit->creer || $user->rights->service->creer)) {
		$licenseKeys = array();
		$result = $licenseKeylist->fetchList('', 'name');
		//print_r($result);
		//print_r($licenseKeylist);
		foreach ($licenseKeylist->dataset as $licenseKey) {
			$licenseKeys[$licenseKey['rowid']] = $licenseKey['name'];
		}
		print_fiche_titre($langs->trans("EditLicense"), '', '');

		print '<form action="' . $_SERVER["PHP_SELF"] . '?id=' . $product->id . '" method="POST">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		print '<input type="hidden" name="action" value="update_license">';
		print '<input type="hidden" name="id" value="' . $product->id . '">';
		print '<table class="border" width="100%">';

		// key
		print '<tr><td>' . $langs->trans("key") . '</td><td colspan="2">';
		print $form->selectarray('fk_base_key', $licenseKeys, $licenseProduct->fk_base_key);
		print '</td></tr>';

		// optioncode
		print '<tr><td>' . $langs->trans("OptionCode") . '</td><td><input name="option_code" size="50" value="' . $licenseProduct->option_code . '"></td><tr>';
		print '</table>';

		print '<center><br><input type="submit" class="button" value="' . $langs->trans("Save") . '">&nbsp;';
		print '<input type="submit" class="button" name="delete" value="' . $langs->trans("Delete") . '">&nbsp;';
		print '<input type="submit" class="button" name="cancel" value="' . $langs->trans("Cancel") . '"></center>';
		print '<br></form>';
	}

	/*
	 * Confirmation de la suppression de la commande
	 */
	if (GETPOSTISSET('delete')) {
		print $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $product->id, $langs->trans('DeleteLicense'), $langs->trans('ConfirmDeleteLicense'), 'confirm_delete', '', 0, 1);
	}
}


if (($action != 'view') && ($action != 'edit_license') && ! GETPOSTISSET('cancel') && ! GETPOSTISSET('delete')) {
	dol_htmloutput_mesg($mesg);
}



// End of page
llxFooter();
$db->close();
