<?php
/* Copyright (C) 2023		Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2025		Francis Appels				<francis.appels@z-application.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    licensemanager/class/actions_licensemanager.class.php
 * \ingroup licensemanager
 * \brief   Example hook overload.
 *
 * TODO: Write detailed description here.
 */

require_once DOL_DOCUMENT_ROOT . '/core/class/commonhookactions.class.php';

/**
 * Class ActionsLicenseManager
 */
class ActionsLicenseManager extends CommonHookActions
{
	/**
	 * @var DoliDB Database handler.
	 */
	public $db;

	/**
	 * @var string Error code (or message)
	 */
	public $error = '';

	/**
	 * @var string[] Errors
	 */
	public $errors = array();


	/**
	 * @var mixed[] Hook results. Propagated to $hookmanager->resArray for later reuse
	 */
	public $results = array();

	/**
	 * @var ?string String displayed by executeHook() immediately after return
	 */
	public $resprints;

	/**
	 * @var int		Priority of hook (50 is used if value is not defined)
	 */
	public $priority;


	/**
	 * Constructor
	 *
	 *  @param	DoliDB	$db      Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}


	/**
	 * Execute action
	 *
	 * @param	array<string,mixed>	$parameters	Array of parameters
	 * @param	CommonObject		$object		The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string				$action		'add', 'update', 'view'
	 * @return	int								Return integer <0 if KO,
	 *                           				=0 if OK but we want to process standard actions too,
	 *											>0 if OK and we want to replace standard actions.
	 */
	public function getNomUrl($parameters, &$object, &$action)
	{
		global $db, $langs, $conf, $user;
		$this->resprints = '';
		return 0;
	}

	/**
	 * Overload the doActions function : replacing the parent's function with the one below
	 *
	 * @param	array<string,mixed>	$parameters		Hook metadata (context, etc...)
	 * @param	CommonObject		$object			The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	?string				$action			Current action (if set). Generally create or edit or null
	 * @param	HookManager			$hookmanager	Hook manager propagated to allow calling another hook
	 * @return	int									Return integer < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		// @phan-suppress-next-line PhanPluginEmptyStatementIf
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {	    // do something only for the context 'somecontext1' or 'somecontext2'
			// Do what you want here...
			// You can for example load and use call global vars like $fieldstosearchall to overwrite them, or update the database depending on $action and GETPOST values.

			if (!$error) {
				$this->results = array('myreturn' => 999);
				$this->resprints = 'A text to show';
				return 0; // or return 1 to replace standard code
			} else {
				$this->errors[] = 'Error message';
				return -1;
			}
		}

		return 0;
	}

	/**
	 * Overload the PrintPageView function : replacing the parent's function with the one below
	 *
	 * @param	array<string,mixed>	$parameters		Hook metadata (context, etc...)
	 * @param	CommonObject		$object			The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	?string				$action			Current action (if set). Generally create or edit or null
	 * @param	HookManager			$hookmanager	Hook manager propagated to allow calling another hook
	 * @return	int									Return integer < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function PrintPageView($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $form, $url_file, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		// @phan-suppress-next-line PhanPluginEmptyStatementIf
		if (in_array($parameters['currentcontext'], array('webportalpage', 'somecontext2'))) {	    // do something only for the context 'somecontext1' or 'somecontext2'
			if ($object->controller == 'orderlist') {
				dol_include_once('/webportal/class/html.formwebportal.class.php');
				$langs->load('licensemanager@licensemanager');
				$form = new FormWebPortal($this->db);
				$url_file = $object->getControllerUrl($object->controller);
				// need core patch to make formList public
				if (isset($object->controllerInstance->formList->arrayfields)) {
					$object->controllerInstance->formList->arrayfields['t.date_livraison']['checked'] = 0;
					$object->controllerInstance->formList->arrayfields['t.total_ht']['checked'] = 0;
					$object->controllerInstance->formList->arrayfields['t.total_tva']['checked'] = 0;
					$object->controllerInstance->formList->arrayfields['t.total_ttc']['checked'] = 0;
					$object->controllerInstance->formList->arrayfields['t.fk_statut']['checked'] = 0;
					$object->controllerInstance->formList->arrayfields['download_link']['checked'] = 0;
				}

				print '<div class="div-table-responsive-no-min">';
				// Print link to connector need core patch to allow download of type 'archive'
				print '<table class="noborder centpercent">';
				print '<tr class="liste_titre">';
				print '<td class="right">';
				print $langs->trans('DownloadModule').' ';
				$filename = dol_sanitizeFileName('module');
				$filedir = $conf->commande->multidir_output[$conf->entity].'/module';
				print $form->getDocumentsLink('commande', $filename, $filedir, '', '', 1);
				print '</td>';
				print '<td class="left">';
				print $langs->trans('DownloadModuleInstructions');
				print '</td>';
				print '</tr>';
				print '</table>';
				print '</div>';

				if (!$error) {
					$this->results = array('myreturn' => 999);
					$this->resprints = 'A text to show';
					return 0; // or return 1 to replace standard code
				} else {
					$this->errors[] = 'Error message';
					return -1;
				}
			}
		}

		return 0;
	}


	/**
	 * Overload the doMassActions function : replacing the parent's function with the one below
	 *
	 * @param	array<string,mixed>	$parameters		Hook metadata (context, etc...)
	 * @param	CommonObject		$object			The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	?string				$action			Current action (if set). Generally create or edit or null
	 * @param	HookManager			$hookmanager	Hook manager propagated to allow calling another hook
	 * @return	int									Return integer < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function doMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			// @phan-suppress-next-line PhanPluginEmptyStatementForeachLoop
			foreach ($parameters['toselect'] as $objectid) {
				// Do action on each object id
			}

			if (!$error) {
				$this->results = array('myreturn' => 999);
				$this->resprints = 'A text to show';
				return 0; // or return 1 to replace standard code
			} else {
				$this->errors[] = 'Error message';
				return -1;
			}
		}

		return 0;
	}


	/**
	 * Overload the addMoreMassActions function : replacing the parent's function with the one below
	 *
	 * @param	array<string,mixed>	$parameters     Hook metadata (context, etc...)
	 * @param	CommonObject		$object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	?string	$action						Current action (if set). Generally create or edit or null
	 * @param	HookManager	$hookmanager			Hook manager propagated to allow calling another hook
	 * @return	int									Return integer < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function addMoreMassActions($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$disabled = 1;

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			$this->resprints = '<option value="0"' . ($disabled ? ' disabled="disabled"' : '') . '>' . $langs->trans("LicenseManagerMassAction") . '</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	/**
	 * Overload the getlinetotalremise function : replacing the parent's function with the one below
	 *
	 * @param	array<string,mixed>	$parameters		Hook metadata (context, etc...)
	 * @param	CommonObject		$object			The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	?string				$action			Current action (if set). Generally create or edit or null
	 * @param	HookManager			$hookmanager	Hook manager propagated to allow calling another hook
	 * @return	int									Return integer < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function getlinetotalremise($parameters, &$object, &$action, $hookmanager)
	{
		if (in_array($parameters['currentcontext'], array('pdfgeneration', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
			if ($object->element == 'propal' && getDolGlobalInt('LICENSEMANAGER_DISABLE_DISCOUNT_ON_PROPOSAL', 0)) {
				$this->results = array('linetotalremise' => 0);
				return 1;
			}
		}

		return 0;
	}

	/**
	 * Execute action before PDF (document) creation
	 *
	 * @param	array<string,mixed>	$parameters	Array of parameters
	 * @param	CommonObject		$object		Object output on PDF
	 * @param	string				$action		'add', 'update', 'view'
	 * @return	int								Return integer <0 if KO,
	 *											=0 if OK but we want to process standard actions too,
	 *											>0 if OK and we want to replace standard actions.
	 */
	public function beforePDFCreation($parameters, &$object, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0;
		$deltemp = array();
		dol_syslog(get_class($this) . '::executeHooks action=' . $action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		// @phan-suppress-next-line PhanPluginEmptyStatementIf
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {		// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}

	/**
	 * Execute action after PDF (document) creation
	 *
	 * @param	array<string,mixed>	$parameters	Array of parameters
	 * @param	CommonDocGenerator	$pdfhandler	PDF builder handler
	 * @param	string				$action		'add', 'update', 'view'
	 * @return	int								Return integer <0 if KO,
	 * 											=0 if OK but we want to process standard actions too,
	 *											>0 if OK and we want to replace standard actions.
	 */
	public function afterPDFCreation($parameters, &$pdfhandler, &$action)
	{
		global $conf, $user, $langs;
		global $hookmanager;

		$outputlangs = $langs;

		$ret = 0;
		$deltemp = array();
		dol_syslog(get_class($this) . '::executeHooks action=' . $action);

		/* print_r($parameters); print_r($object); echo "action: " . $action; */
		// @phan-suppress-next-line PhanPluginEmptyStatementIf
		if (in_array($parameters['currentcontext'], array('somecontext1', 'somecontext2'))) {
			// do something only for the context 'somecontext1' or 'somecontext2'
		}

		return $ret;
	}

	/**
	 * Overloading the printFieldListSelect function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function printFieldListSelect($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user;

		$error = 0; // Error counter
		$this->resprints = '';

		if (in_array($parameters['currentcontext'], array('orderlistdetail', 'webportalpage'))) {
			$user->loadRights('licensemanager');
			if ($user->rights->licensemanager->licensemanager->read) {
				$this->resprints = ", lo.note as license_note, lo.identification, lo.date_valid as license_date_valid, lo.status as license_status";
			}
		}

		if (in_array($parameters['currentcontext'], array('orderlistdetail'))) {
			$user->loadRights('licensemanager');
			if ($user->rights->licensemanager->licensemanager->read) {
				$this->resprints .= ', (SELECT GROUP_CONCAT(cdl.fk_product, ",") FROM '.$this->db->prefix().'commandedet as cdl INNER JOIN '.$this->db->prefix().'license_orderdet lod ON lo.rowid = lod.fk_license_order INNER JOIN '.$this->db->prefix().'license_product lp ON lp.fk_product = cdl.fk_product AND lp.rowid = lod.fk_license_product WHERE cdl.fk_commande = c.rowid) as product_ids';
			}
		}

		if (in_array($parameters['currentcontext'], array('webportalpage'))) {
			$user->loadRights('licensemanager');
			if ($user->rights->licensemanager->licensemanager->read) {
				$this->resprints .= ', (SELECT GROUP_CONCAT(cdl.fk_product, ",") FROM '.$this->db->prefix().'commandedet as cdl INNER JOIN '.$this->db->prefix().'license_orderdet lod ON lo.rowid = lod.fk_license_order INNER JOIN '.$this->db->prefix().'license_product lp ON lp.fk_product = cdl.fk_product AND lp.rowid = lod.fk_license_product WHERE cdl.fk_commande = t.rowid) as product_ids';
			}
		}

		if (! $error) {
			return 0;
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	/**
	 * Overloading the printFieldListFrom function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function printFieldListFrom($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user;

		$error = 0; // Error counter

		$this->resprints = '';


		if (in_array($parameters['currentcontext'], array('orderlistdetail'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				$this->resprints .= " LEFT JOIN " . $this->db->prefix() . "license_order lo on c.rowid = lo.fk_commande";
			}
		}
		if (in_array($parameters['currentcontext'], array('webportalpage'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				$this->resprints .= " LEFT JOIN " . $this->db->prefix() . "license_order lo on t.rowid = lo.fk_commande";
			}
		}

		if (! $error) {
			return 0;
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	/**
	 * Overloading the printFieldListSearchParam function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function printFieldListSearchParam($parameters, &$object, &$action, $hookmanager)
	{
		global $user;

		$error = 0; // Error counter

		/* for future dolibarr when product list has this */
		if (in_array($parameters['currentcontext'], array('orderlistdetail', 'webportalpage'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				$param = "&search_license_note=" . GETPOST('search_license_note', 'alpha');
				$param .= "&search_identification=" . GETPOST('search_identification', 'alpha');
				$param .= "&search_license_status=" . GETPOST('search_license_status', 'int');
				$this->resprints = $param;
			}
		}

		if (! $error) {
			return 0;
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	/**
	 * Overloading the printFieldListSelect function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function printFieldListWhere($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user;

		$error = 0; // Error counter
		$this->resprints = '';

		if (in_array($parameters['currentcontext'], array('orderlistdetail', 'webportalpage'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				$search_license_note = GETPOST('search_license_note', 'alpha');
				if ($search_license_note != '') {
					$this->resprints .= natural_search('lo.note', $search_license_note);
				}
				$search_identification = GETPOST('search_identification', 'alpha');
				if ($search_identification != '') {
					$this->resprints .= natural_search('lo.identification', $search_identification);
				} else {
					$this->resprints .= ' AND lo.identification IS NOT NULL';
				}
				$search_status = GETPOST('search_license_status', 'int');
				if ($search_status >= 0 && $search_status != '') {
					$this->resprints .= natural_search('lo.status', $search_status, 1);
				}
			}
		}

		if (! $error) {
			return 0;
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	/**
	 * Overloading the printFieldListOption function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function printFieldListOption($parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $user, $form;

		$error = 0; // Error counter
		$this->resprints = '';

		if (in_array($parameters['currentcontext'], array('orderlistdetail', 'webportalpage'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				$langs->load('licensemanager@licensemanager');
				dol_include_once('/licensemanager/class/licenseorder.class.php');
				$search_identification = GETPOST('search_identification', 'alpha');
				$this->resprints .= '<td class="liste_titre right minwidth150">';
				$this->resprints .= '<input class="flat" type="text" size="4" name="search_identification" value="' . $search_identification . '">';
				$this->resprints .= '</td>';
				$this->resprints .= '<td class="liste_titre right minwidth150">';
				$this->resprints .= '</td>';
				$search_license_note = GETPOST('search_license_note', 'alpha');
				$this->resprints .= '<td class="liste_titre right">';
				$this->resprints .= '<input class="flat" type="text" size="4" name="search_license_note" value="' . $search_license_note . '">';
				$this->resprints .= '</td>';
				$search_status = GETPOST('search_license_status', 'alpha');
				$this->resprints .= '<td class="liste_titre right">';
				$liststatus = array(
					Licenseorder::STATUS_DRAFT => $langs->trans("Draft"),
					Licenseorder::STATUS_VALIDATED => $langs->trans("Active"),
					Licenseorder::STATUS_CANCELED => $langs->trans("Expired")
				);
				$this->resprints .= $form->selectarray('search_license_status', $liststatus, $search_status, 1, 0, 0, '', 0, 0, 0, '', 'search_status width100 onrightofpage');
				$this->resprints .= '</td>';
				$this->resprints .= '<td></td>';
			}
		}

		if (! $error) {
			return 0;
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	/**
	 * Overloading the printFieldListTitle function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function printFieldListTitle($parameters, &$object, &$action, $hookmanager)
	{
		global $conf, $user, $langs, $url_file;

		$error = 0; // Error counter
		$this->resprints = '';

		if (in_array($parameters['currentcontext'], array('orderlistdetail'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				$langs->load('licensemanager@licensemanager');
				$sortfield = $parameters['sortfield'];
				$sortorder = $parameters['sortorder'];
				$param = $parameters['param'];
				$this->resprints .= getTitleFieldOfList($langs->trans('License'), 0, $_SERVER["PHP_SELF"], 'identification', '', $param, '', $sortfield, $sortorder, 'right ');
				$this->resprints .= getTitleFieldOfList($langs->trans('DateValid'), 0, $_SERVER["PHP_SELF"], 'license_date_valid', '', $param, '', $sortfield, $sortorder, 'right ');
				$this->resprints .= getTitleFieldOfList($langs->trans('LicenseNote'), 0, $_SERVER["PHP_SELF"], 'license_note', '', $param, '', $sortfield, $sortorder, 'right ');
				$this->resprints .= getTitleFieldOfList($langs->trans('LicenseStatus'), 0, $_SERVER["PHP_SELF"], 'license_status', '', $param, '', $sortfield, $sortorder, 'right ');
				$this->resprints .= getTitleFieldOfList($langs->trans('LicenseDoc'));
			}
		}

		if (in_array($parameters['currentcontext'], array('webportalpage'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				$langs->load('licensemanager@licensemanager');
				$sortfield = $parameters['sortfield'];
				$sortorder = $parameters['sortorder'];
				$sortList = $parameters['sortList'];
				$totalarray = $parameters['totalarray'];

				$tableKey = 'identification';
				$tableOrder = '';
				if (array_key_exists($tableKey, $sortList)) {
					$tableOrder = strtolower($sortList[$tableKey]);
				}
				$url_param = $url_file . '&sortfield=' . $tableKey . '&sortorder=' . ($tableOrder == 'desc' ? 'asc' : 'desc') . $param;
				$this->resprints .= '<th class="right" data-col="'.dol_escape_htmltag($tableKey ).'"  scope="col"' . ($tableOrder != '' ? ' table-order="' . $tableOrder . '"' : '') . '>';
				$this->resprints .= '<a href="' . $url_param . '">';
				$this->resprints .= $langs->trans('License');
				$this->resprints .= '</a>';
				$this->resprints .= '</th>';
				$totalarray['nbfield']++;

				$tableKey = 'license_date_valid';
				$tableOrder = '';
				if (array_key_exists($tableKey, $sortList)) {
					$tableOrder = strtolower($sortList[$tableKey]);
				}
				$url_param = $url_file . '&sortfield=' . $tableKey . '&sortorder=' . ($tableOrder == 'desc' ? 'asc' : 'desc') . $param;
				$this->resprints .= '<th class="right" data-col="'.dol_escape_htmltag($tableKey ).'"  scope="col"' . ($tableOrder != '' ? ' table-order="' . $tableOrder . '"' : '') . '>';
				$this->resprints .= '<a href="' . $url_param . '">';
				$this->resprints .= $langs->trans('DateValid');
				$this->resprints .= '</a>';
				$this->resprints .= '</th>';
				$totalarray['nbfield']++;

				$tableKey = 'license_note';
				$tableOrder = '';
				if (array_key_exists($tableKey, $sortList)) {
					$tableOrder = strtolower($sortList[$tableKey]);
				}
				$url_param = $url_file . '&sortfield=' . $tableKey . '&sortorder=' . ($tableOrder == 'desc' ? 'asc' : 'desc') . $param;
				$this->resprints .= '<th class="right" data-col="'.dol_escape_htmltag($tableKey ).'"  scope="col"' . ($tableOrder != '' ? ' table-order="' . $tableOrder . '"' : '') . '>';
				$this->resprints .= '<a href="' . $url_param . '">';
				$this->resprints .= $langs->trans('LicenseNote');
				$this->resprints .= '</a>';
				$this->resprints .= '</th>';
				$totalarray['nbfield']++;

				$tableKey = 'license_status';
				$tableOrder = '';
				if (array_key_exists($tableKey, $sortList)) {
					$tableOrder = strtolower($sortList[$tableKey]);
				}
				$url_param = $url_file . '&sortfield=' . $tableKey . '&sortorder=' . ($tableOrder == 'desc' ? 'asc' : 'desc') . $param;
				$this->resprints .= '<th data-col="'.dol_escape_htmltag($tableKey ).'"  scope="col"' . ($tableOrder != '' ? ' table-order="' . $tableOrder . '"' : '') . '>';
				$this->resprints .= '<a href="' . $url_param . '">';
				$this->resprints .= $langs->trans('LicenseStatus');
				$this->resprints .= '</a>';
				$this->resprints .= '</th>';
				$totalarray['nbfield']++;

				$this->resprints .= '<th scope="col">';
				$this->resprints .= $langs->trans('LicenseDoc');
				$this->resprints .= '</th>';
				$totalarray['nbfield']++;
			}
		}





		if (! $error) {
			return 0;
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
	}

	/**
	 * Overloading the printFieldListValue function : replacing the parent's function with the one below
	 *
	 * @param   array           $parameters     Hook metadatas (context, etc...)
	 * @param   CommonObject    $object         The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param   string          $action         Current action (if set). Generally create or edit or null
	 * @param   HookManager     $hookmanager    Hook manager propagated to allow calling another hook
	 * @return  int                             < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function printFieldListValue($parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $user, $conf, $form;

		$error = 0; // Error counter
		$this->resprints = '';

		if (in_array($parameters['currentcontext'], array('orderlistdetail', 'webportalpage'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				dol_include_once('/licensemanager/class/licenseorder.class.php');
				$langs->load('licensemanager@licensemanager');
				$obj = $parameters['obj'];
				$product_ids = explode(",", $obj->product_ids);
				$tooltip = '';
				if (!empty($product_ids) && is_array($product_ids)) {
					// Check that product in license is in the order
					foreach ($product_ids as $pid) {
						if (!empty($pid) && $pid > 0) {
							$product = new Product($this->db);
							$product->fetch($pid);
							if (!empty($product->id)) {
								$tooltip .= $product->label . ' ';
							}
						}

					}
				}
				$this->resprints .= '<td class="right" title="'.dolPrintHTMLForAttribute($tooltip).'" >' . $obj->identification . '</td>';
				$this->resprints .= '<td class="right">';
				if (!empty($obj->license_date_valid)) {
					$date_valid = $this->db->jdate($obj->license_date_valid);
					if ($date_valid < dol_now() && !preg_match('/(debug)|(free)|(demo)|(test)/i', $obj->license_note) && !preg_match('/(debug)|(free)|(demo)|(test)/i', $obj->identification)) {
						$this->resprints .= img_picto($langs->trans('Expired'), 'warning', '', 0);
					}
					$this->resprints .= dol_print_date($date_valid);
				}
				$this->resprints .= '</td>';
				$this->resprints .= '<td class="right">' . $obj->license_note . '</td>';
				$licenseOrder = new Licenseorder($this->db);
				$this->resprints .= '<td class="right">' . $licenseOrder->libStatut($obj->license_status, 2) . '</td>';
				if (in_array($parameters['currentcontext'], array('webportalpage'))) {
					/** @var FormWebPortal $form  */
					//$this->resprints .= '<td class="right">';
					//$product = new Product($this->db);
					//$product->fetch(5);
					//$this->resprints .= $product->show_photos('product', $conf->product->multidir_output[$product->entity], 1, 1, 0, 0, 0, 120, 160, 0, 0, 0, '', 'photoref photokanban');
					//$this->resprints .= '</td>';
					// Download link
					$order = new Commande($this->db);
					$element = $order->element;
					$this->resprints .= '<td class="nowraponall" data-label="' . $langs->trans('File') . '">';
					$filename = dol_sanitizeFileName($obj->ref);
					$filedir = $conf->{$element}->multidir_output[$obj->element_entity] . '/' . dol_sanitizeFileName($obj->ref);
					$this->resprints .= $form->getDocumentsLink($element, $filename, $filedir, '_', '', 1);
					$this->resprints .= '</td>';
				}
			}
		}

		if (! $error) {
			return 0;
		} else {
			return -1;
		}
	}

	/**
	 * Overload the loadDataForCustomReports function : returns data to complete the customreport tool
	 *
	 * @param	array<string,mixed>	$parameters		Hook metadata (context, etc...)
	 * @param	?string				$action 		Current action (if set). Generally create or edit or null
	 * @param	HookManager			$hookmanager    Hook manager propagated to allow calling another hook
	 * @return	int									Return integer < 0 on error, 0 on success, 1 to replace standard code
	 */
	public function loadDataForCustomReports($parameters, &$action, $hookmanager)
	{
		global $langs;

		$langs->load("licensemanager@licensemanager");

		$this->results = array();

		$head = array();
		$h = 0;

		if ($parameters['tabfamily'] == 'licensemanager') {
			$head[$h][0] = dol_buildpath('/module/index.php', 1);
			$head[$h][1] = $langs->trans("Home");
			$head[$h][2] = 'home';
			$h++;

			$this->results['title'] = $langs->trans("LicenseManager");
			$this->results['picto'] = 'licensemanager@licensemanager';
		}

		$head[$h][0] = 'customreports.php?objecttype=' . $parameters['objecttype'] . (empty($parameters['tabfamily']) ? '' : '&tabfamily=' . $parameters['tabfamily']);
		$head[$h][1] = $langs->trans("CustomReports");
		$head[$h][2] = 'customreports';

		$this->results['head'] = $head;

		$arrayoftypes = array();
		//$arrayoftypes['licensemanager_myobject'] = array('label' => 'MyObject', 'picto'=>'myobject@licensemanager', 'ObjectClassName' => 'MyObject', 'enabled' => isModEnabled('licensemanager'), 'ClassPath' => "/licensemanager/class/myobject.class.php", 'langs'=>'licensemanager@licensemanager')

		$this->results['arrayoftype'] = $arrayoftypes;

		return 0;
	}



	/**
	 * Overload the restrictedArea function : check permission on an object
	 *
	 * @param	array<string,mixed>	$parameters		Hook metadata (context, etc...)
	 * @param	string				$action			Current action (if set). Generally create or edit or null
	 * @param	HookManager			$hookmanager	Hook manager propagated to allow calling another hook
	 * @return	int									Return integer <0 if KO,
	 *												=0 if OK but we want to process standard actions too,
	 *												>0 if OK and we want to replace standard actions.
	 */
	public function restrictedArea($parameters, &$action, $hookmanager)
	{
		global $user;

		if ($parameters['features'] == 'myobject') {
			if ($user->hasRight('licensemanager', 'myobject', 'read')) {
				$this->results['result'] = 1;
				return 1;
			} else {
				$this->results['result'] = 0;
				return 1;
			}
		}

		return 0;
	}

	/**
	 * Execute action completeTabsHead
	 *
	 * @param	array<string,mixed>	$parameters		Array of parameters
	 * @param	CommonObject		$object			The object to process (an invoice if you are in invoice module, a propale in propale's module, etc...)
	 * @param	string				$action			'add', 'update', 'view'
	 * @param	Hookmanager			$hookmanager	Hookmanager
	 * @return	int									Return integer <0 if KO,
	 *												=0 if OK but we want to process standard actions too,
	 *												>0 if OK and we want to replace standard actions.
	 */
	public function completeTabsHead(&$parameters, &$object, &$action, $hookmanager)
	{
		global $langs, $conf, $user;

		if (!isset($parameters['object']->element)) {
			return 0;
		}
		if ($parameters['mode'] == 'remove') {
			// used to make some tabs removed
			return 0;
		} elseif ($parameters['mode'] == 'add') {
			$langs->load('licensemanager@licensemanager');
			// used when we want to add some tabs
			$counter = count($parameters['head']);
			$element = $parameters['object']->element;
			$id = $parameters['object']->id;
			// verifier le type d'onglet comme member_stats où ça ne doit pas apparaitre
			// if (in_array($element, ['societe', 'member', 'contrat', 'fichinter', 'project', 'propal', 'commande', 'facture', 'order_supplier', 'invoice_supplier'])) {
			if (in_array($element, ['context1', 'context2'])) {
				$datacount = 0;

				$parameters['head'][$counter][0] = dol_buildpath('/licensemanager/licensemanager_tab.php', 1) . '?id=' . $id . '&amp;module=' . $element;
				$parameters['head'][$counter][1] = $langs->trans('LicenseManagerTab');
				if ($datacount > 0) {
					$parameters['head'][$counter][1] .= '<span class="badge marginleftonlyshort">' . $datacount . '</span>';
				}
				$parameters['head'][$counter][2] = 'licensemanageremails';
				$counter++;
			}
			if ($counter > 0 && (int) DOL_VERSION < 14) {  // @phpstan-ignore-line
				$this->results = $parameters['head'];
				// return 1 to replace standard code
				return 1;
			} else {
				// From V14 onwards, $parameters['head'] is modifiable by reference
				return 0;
			}
		} else {
			// Bad value for $parameters['mode']
			return -1;
		}
	}

	/* Add other hook methods here... */
}
