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
 *   	\file       licensemanager/licenseorder.php
 *		\ingroup    licensemanager order
 *		\brief      This file is used to show and configure the licenses of the ordered products/services
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
// Change this following line to use the correct relative path from htdocs
require_once DOL_DOCUMENT_ROOT . '/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/order.lib.php';

dol_include_once('/licensemanager/class/licenseorder.class.php');
dol_include_once('/licensemanager/class/licenseproduct.class.php');
dol_include_once('/licensemanager/class/licensekeylist.class.php');
dol_include_once('/licensemanager/class/licenselist.class.php');
dol_include_once('/licensemanager/class/pdf_license.class.php');


// Load traductions files requiredby by page
$langs->load("orders");
$langs->load("other");
$langs->load("licensemanager@licensemanager");

$error = 0;
// Get parameters
$id			= GETPOST('id', 'int');
$licenseid	= GETPOST('licenseid', 'int'); // For single license
$ref		= GETPOST('ref', 'alpha');
$action		= GETPOST('action', 'alpha');
$actionDet  = array();
$myparam	= GETPOST('myparam', 'alpha');

// Protection if external user
if ($user->societe_id > 0) {
	accessforbidden();
}

$order = new Commande($db);
if ($id > 0 || ! empty($ref)) {
	if ($order->fetch($id, $ref) <= 0) {
		$mesg = '<font class="error">' . $langs->trans("Error") . 'Order not found'  . '</font>';
		$action = '';
	}
} else {
	$mesg = '<font class="error">' . $langs->trans("Error") . 'No order'  . '</font>';
	$action = '';
}

$canceled = false;

/*******************************************************************
 * ACTIONS
 *
 * Put here all code to do according to value of "action" parameter
 ********************************************************************/

if (strstr($action, 'set')) {
	$actionDet = explode("_", $action); // element 2 is field, element 3 is licenseorder id
	$licenseOrder = new Licenseorder($db);
	if ($licenseOrder->fetch($actionDet[2], 0, 0) > 0) {
		if ($actionDet[1] == 'identification') {
			$licenseOrder->identification = GETPOST('identification', 'alpha');
			$result = $licenseOrder->update($user);
		} elseif ($actionDet[1] == 'note') {
			$licenseOrder->note = GETPOST('note', 'alpha');
			$result = $licenseOrder->update($user);
		} elseif ($actionDet[1] == 'outputmode') {
			$licenseOrder->output_mode = GETPOST('output_mode', 'alpha');
			$result = $licenseOrder->update($user);
		}
	}

	if (! $result > 0) $error++;

	if (! $error) {
		$db->commit();
		$mesg = '<font class="ok">' . $langs->trans("Saved") . '</font>';
	} else {
		$db->rollback();
		$mesg = '<font class="error">' . $langs->trans("Error") . ' ' . $action . '</font>';
	}
} elseif ($action == 'generate_licenses') {
	// generate licenses
	$licenseOrder = new Licenseorder($db);
	if ($licenseOrder->fetchList("fk_commande = $order->id", '') > 0) {
		foreach ($licenseOrder->dataset as $licenseOrderData) {
			$licenseOrderDet = new Licenseorderdet($db);
			$currentLicenseOrder = new Licenseorder($db);
			$generated = false;

			if ($licenseOrderData['identification'] != '') {
				if (($currentLicenseOrder->fetch($licenseOrderData['rowid'], 0, 0) > 0) && ($licenseOrderDet->fetchList("fk_license_order = $currentLicenseOrder->id") > 0)) {
					foreach ($licenseOrderDet->dataset as $data) {
						$key = $currentLicenseOrder->generate($user, $data);
						if (is_string($key)) {
							$db->commit();
							$generated = true;
						} else {
							$db->rollback();
							$generated = false;
							$mesg = '<font class="error">' . $langs->trans("Error") . ' ' . $langs->trans($currentLicenseOrder->error) . '</font>';
							break;
						}
					}
					if ($generated) {
						$mesg = '<font class="ok">' . $langs->trans("Generated") . '</font>';
						$currentLicenseOrder->validate($user);
					}
				}
			} else {
				$mesg = '<font class="error">' . $langs->trans("MissingIdentification") . '</font>';
				break;
			}
		}
	}
} elseif ($action == 'renew_licenses') {
	// Renew licenses
	$licenseOrder = new Licenseorder($db);
	if ($licenseOrder->fetchList("fk_commande = $order->id", '') > 0) {
		foreach ($licenseOrder->dataset as $licenseOrderData) {
			$licenseOrderDet = new Licenseorderdet($db);
			$currentLicenseOrder = new Licenseorder($db);
			$renewed = false;

			if (($currentLicenseOrder->fetch($licenseOrderData['rowid'], 0, 0) > 0) && ($licenseOrderDet->fetchList("fk_license_order = $currentLicenseOrder->id") > 0)) {
				foreach ($licenseOrderDet->dataset as $data) {
					$key = $currentLicenseOrder->renew($user, $data);
					if (is_string($key)) {
						$db->commit();
						$renewed = true;
					} else {
						$db->rollback();
						$renewed = false;
						$mesg = '<font class="error">' . $langs->trans("Error") . ' ' . $langs->trans($currentLicenseOrder->error) . '</font>';
						break;
					}
				}
				if ($renewed) {
					$mesg = '<font class="ok">' . $langs->trans("Renewed") . '</font>';
				}
			}
		}
	}
} elseif ($action == 'cancel_licenses') {
	// de-activate licenses
	$orderRefs = array();

	$licenseOrder = new Licenseorder($db);
	if ($licenseOrder->fetchList("fk_commande = $order->id", '') > 0) {
		$orderRefs[] = $order->ref;
		foreach ($licenseOrder->dataset as $licenseOrderData) {
			$currentLicenseOrder = new Licenseorder($db);

			if ($currentLicenseOrder->fetch($licenseOrderData['rowid'], 0, 0) > 0) {

				$result = $currentLicenseOrder->cancel($user);
				if ($result > 0) {
					$canceled = true;
					$otherOrder = new Commande($db);
					if ($otherOrder->fetch($currentLicenseOrder->fk_commande) > 0) {
						if (!in_array($otherOrder->ref, $orderRefs)) {
							$orderRefs[] = $otherOrder->ref;
							$orderRef = $otherOrder->ref;
						} else {
							$orderRef = $order->ref;
						}
					}
					$mesg = '<font class="ok">' . $langs->trans("LicenseForOrderCanceled", implode(",", $orderRefs)) . '</font>';
				} else {
					$mesg = '<font class="error">' . $langs->trans('Error') . ' ' . $langs->trans('AlreadyCanceled') . '</font>';
				}
			}
		}
	}
} elseif ($action == 'cancel_license') {
	// de-activate license
	$currentLicenseOrder = new Licenseorder($db);

	if ($currentLicenseOrder->fetch($licenseid, 0, 0) > 0) {
		$result = $currentLicenseOrder->cancel($user);

		if ($result > 0) {
			$canceled = true;
			$otherOrder = new Commande($db);
			if ($otherOrder->fetch($currentLicenseOrder->fk_commande) > 0) {
				$orderRef = $otherOrder->ref;
			} else {
				$orderRef = '';
			}
			$mesg = '<font class="ok">' . $langs->trans("LicenseForOrderCanceled", $orderRef) . '</font>';
		} else {
			$mesg = '<font class="error">' . $langs->trans('Error') . ' ' . $langs->trans('AlreadyCanceled') . '</font>';
		}
	}
}
if ($action == 'generate_doc' || $canceled) {
	// generate document
	$licenseOrderList = new Licenseorder($db);
	if ($licenseOrderList->fetchList("fk_commande = $order->id", '') > 0) {
		$pdfLicense = new pdf_license($db);
		if ($pdfLicense->write_file($order, $langs) > 0) {
			$mesg = '<font class="ok">' . $langs->trans("Generated") . '</font>';
		} else {
			$mesg = '<font class="error">' . $langs->trans("Error") . ' ' . $action . '</font>';
		}
	}
}

/***************************************************
 * VIEW
 *
 * Put here all code to build page
 ****************************************************/

llxHeader('', $langs->trans('OrderCard'), '');

$form = new Form($db);


// Put here content of your page

$licenseModes = array();



$soc = new Societe($db);
$soc->fetch($order->socid);

$licenseOrder = new Licenseorder($db);
$multiLicense = new License();

$author = new User($db);
$author->fetch($order->user_author_id);

$head = commande_prepare_head($order);
print dol_get_fiche_head($head, 'licenseorder', $langs->trans("CustomerOrder"), 0, 'order');

print '<table class="border" width="100%">';

// Ref
print '<tr><td width="18%">' . $langs->trans('Ref') . '</td>';
print '<td colspan="3">';
print $form->showrefnav($order, 'ref', '', 1, 'ref', 'ref');
print '</td>';
print '</tr>';

if ($licenseOrder->fetchList("fk_commande = $order->id", '') > 0) {
	$licenseOrderCount = count($licenseOrder->dataset);
	// Ref commande client
	print '<tr><td width="18%">';
	print $langs->trans('RefCustomer') . '</td>';
	print '<td colspan="2">' . $order->ref_client;
	print '</td>';

	// Third party
	print '<tr><td>' . $langs->trans('Company') . '</td>';
	print '<td colspan="3">' . $soc->getNomUrl(1) . '</td>';
	print '</tr>';

	// Date
	print '<tr><td>' . $langs->trans('Date') . '</td>';
	print '<td colspan="3">' . dol_print_date($order->date, 'daytext') . '</td>';
	print '</tr>';

	// Licenses qty
	print '<tr><td>' . $langs->trans('Licenses') . '</td>';
	print '<td colspan="3">' . $licenseOrderCount . '</td>';
	print '</tr>';
} else {
	// no product with license
	print '<tr><td width="18%">' . $langs->trans('License') . '</td>';
	print '<td colspan="3">';
	print $langs->trans('NoProductWithLicense');
	print '</td>';
	print '</tr>';
}


print '</table><br>';


/**
 *  license Orders grouped by license order
 *
 */
if ($licenseOrderCount > 0) {
	foreach ($licenseOrder->dataset as $licenseOrderData) {
		print '<table class="border" width="100%">';
		// License Identification
		print '<tr><td height="10" width="20%">';
		print '<table class="nobordernopadding" width="100%"><tr><td>';
		print $langs->trans('LicenseIdentification');
		print '</td>';

		if ($action != 'edit_identification') print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=edit_identification' . $licenseOrderData['rowid'] . '&amp;id=' . $order->id . '&token='.newToken().'">' . img_edit($langs->trans('SetIdentification'), 1) . '</a></td>';
		print '</tr></table>';
		print '</td><td>';
		if ($action == 'edit_identification' . $licenseOrderData['rowid']) {
			print '<form name="set_identification_"' . $licenseOrderData['rowid'] . ' action="' . $_SERVER["PHP_SELF"] . '?id=' . $order->id . '" method="post">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
			print '<input type="hidden" name="action" value="set_identification_' . $licenseOrderData['rowid'] . '">';
			print '<input type="text" name="identification" value="' . $licenseOrderData['identification'] . '">';
			print '<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
			print '</form>';
		} else {
			print $licenseOrderData['identification'];
		}
		print '</td>';
		print '</tr>';

		// License note
		print '<tr><td height="10" width="20%">';
		print '<table class="nobordernopadding" width="100%"><tr><td>';
		print $langs->trans('LicenseNote');
		print '</td>';

		if ($action != 'edit_note') print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=edit_note' . $licenseOrderData['rowid'] . '&amp;id=' . $order->id . '&token='.newToken().'">' . img_edit($langs->trans('SetNote'), 1) . '</a></td>';
		print '</tr></table>';
		print '</td><td>';
		if ($action == 'edit_note' . $licenseOrderData['rowid']) {
			print '<form name="set_note_"' . $licenseOrderData['rowid'] . ' action="' . $_SERVER["PHP_SELF"] . '?id=' . $order->id . '" method="post">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
			print '<input type="hidden" name="action" value="set_note_' . $licenseOrderData['rowid'] . '">';
			print '<input type="text" name="note" value="' . $licenseOrderData['note'] . '">';
			print '<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
			print '</form>';
		} else {
			print $licenseOrderData['note'];
		}
		print '</td>';
		print '</tr>';

		// License output mode
		print '<tr><td height="10" width="20%">';
		print '<table class="nobordernopadding" width="100%"><tr><td>';
		print $langs->trans('LicenseOutputMode');
		print '</td>';
		$outputModes = Licensekeylist::getOutputModes();
		if ($action != 'edit_outputmode') print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?action=edit_outputmode' . $licenseOrderData['rowid'] . '&amp;id=' . $order->id . '&token='.newToken().'">' . img_edit($langs->trans('SetOutputMode'), 1) . '</a></td>';
		print '</tr></table>';
		print '</td><td>';
		if ($action == 'edit_outputmode' . $licenseOrderData['rowid']) {
			print '<form name="set_outputmode_"' . $licenseOrderData['rowid'] . ' action="' . $_SERVER["PHP_SELF"] . '?id=' . $order->id . '" method="post">';
			print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
			print '<input type="hidden" name="action" value="set_outputmode_' . $licenseOrderData['rowid'] . '">';
			print $form->selectarray('output_mode', $outputModes, $licenseOrderData['outputmode']);
			print '<input type="submit" class="button" value="' . $langs->trans('Modify') . '">';
			print '</form>';
		} else {
			print $outputModes[$licenseOrderData['output_mode']];
		}
		print '</td>';
		print '</tr>';

		$licenseOrderDet = new Licenseorderdet($db);
		$currentLicenseOrder = new Licenseorder($db);

		if (($currentLicenseOrder->fetch($licenseOrderData['rowid'], 0, 0) > 0) && ($licenseOrderDet->fetchList("fk_license_order = $currentLicenseOrder->id") > 0)) {
			print '<table class="liste" width="100%">';
			$colnr = 0;
			print '<tr class="liste_titre">';
			print '<td>' . $langs->trans("Product") . '</td>';
			$colnr++;
			print '<td align="center">' . $langs->trans("LicenseType") . '</td>';
			$colnr++;
			print '<td align="center">' . $langs->trans("LicenseName") . '</td>';
			$colnr++;
			print '<td align="center">' . $langs->trans("DateCreate") . '</td>';
			$colnr++;
			print '<td align="center">' . $langs->trans("DateExpire") . '</td>';
			$colnr++;
			print '<td align="center">' . $langs->trans("Status") . '</td>';
			$colnr++;
			print '<td align="center">' . $langs->trans("LicenseKey") . '</td>';
			$colnr++;
			print '<td align="center">' . $langs->trans("Action") . '</td>';
			$colnr++;
			print "</tr>\n";
			$var = true;
			$multiLicense->key_mode = $currentLicenseOrder->key_mode;
			$multiLicense->output_mode = $currentLicenseOrder->output_mode;
			$multiLicense->code = '';
			foreach ($licenseOrderDet->dataset as $data) {
				$var = !$var;
				print "<tr " . $bc[$var] . ">";
				$currentLicenseOrder->licenseOrderDetList($form, $data, $multiLicense, $order);
				print '</tr>';
			}

			// TODO show other licenses of same customer and identification of older orders
			$otherLicenseOrders = new Licenseorder($db);
			if (isset($currentLicenseOrder->identification) && $otherLicenseOrders->fetchList("fk_customer = $order->socid AND identification = '$currentLicenseOrder->identification'") > 0) {
				foreach ($otherLicenseOrders->dataset as $licenseOrderData) {
					$otherLicenOrderId = $licenseOrderData['rowid'];
					if ($otherLicenOrderId < $currentLicenseOrder->id) {
						$otherLicenseOrdersDet = new Licenseorderdet($db);
						if ($otherLicenseOrdersDet->fetchList("fk_license_order = $otherLicenOrderId") > 0) {
							foreach ($otherLicenseOrdersDet->dataset as $data) {
								$otherLicenseOrder = new Licenseorder($db);
								if ($otherLicenseOrder->fetch($data['fk_license_order'], 0, 0) > 0) {
									$otherOrder = new Commande($db);
									if ($otherOrder->fetch($otherLicenseOrder->fk_commande) > 0) {
										// show other license orders
										$var = !$var;
										$multiLicense->key_mode = $otherLicenseOrder->key_mode;
										print "<tr " . $bc[$var] . ">";
										$otherLicenseOrder->licenseOrderDetList($form, $data, $multiLicense, $order, $otherOrder);
										print '</tr>';
									}
								}
							}
						}
					}
				}
			}

			if ($multiLicense->code) {
				// print multilicense
				print '<tr><td colspan="' . ($colnr - 1) . '" valign="center" align="center">' . $langs->trans('MultiLicense');
				print $currentLicenseOrder->htmlLicense($multiLicense);
				print '</td>';
				print '</tr>';
			}

			print '</table><br>';
		}
		print '</table><br>';
	}
	print '<div class="tabsAction">';
	print dolGetButtonAction('', $langs->trans('RenewLicense'), 'default', $_SERVER["PHP_SELF"] . '?id=' . $order->id . '&amp;action=renew_licenses&token='.newToken());
	print dolGetButtonAction('', $langs->trans('GenerateLicense'), 'default', $_SERVER["PHP_SELF"] . '?id=' . $order->id . '&amp;action=generate_licenses&token='.newToken());
	if ($order->statut == 0) {
		print dolGetButtonAction('', $langs->trans('GenerateLicenseDoc'), 'default', $_SERVER["PHP_SELF"] . '?id=' . $order->id . '&amp;action=generate_doc&token='.newToken(), '', 0);
	} else {
		print dolGetButtonAction('', $langs->trans('GenerateLicenseDoc'), 'default', $_SERVER["PHP_SELF"] . '?id=' . $order->id . '&amp;action=generate_doc&token='.newToken());
	}
	print dolGetButtonAction('', $langs->trans('CancelLicense'), 'danger', $_SERVER["PHP_SELF"] . '?id=' . $order->id . '&amp;action=cancel_licenses&token='.newToken());
	print '</div>';
}

dol_htmloutput_mesg($mesg);
// End of page
llxFooter();
$db->close();
