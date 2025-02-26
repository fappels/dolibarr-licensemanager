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

require_once DOL_DOCUMENT_ROOT.'/core/class/commonhookactions.class.php';

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
			$this->resprints = '<option value="0"'.($disabled ? ' disabled="disabled"' : '').'>'.$langs->trans("LicenseManagerMassAction").'</option>';
		}

		if (!$error) {
			return 0; // or return 1 to replace standard code
		} else {
			$this->errors[] = 'Error message';
			return -1;
		}
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
		dol_syslog(get_class($this).'::executeHooks action='.$action);

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
		dol_syslog(get_class($this).'::executeHooks action='.$action);

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

		if (in_array($parameters['currentcontext'], array('orderlist'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				$this->resprints = ", lo.note as license_note, lo.identification, lo.date_valid as license_date_valid, lo.status as license_status";
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


		if (in_array($parameters['currentcontext'], array('orderlist'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				$this->resprints .= " LEFT JOIN ".$this->db->prefix()."license_order lo on c.rowid = lo.fk_commande";
				//$this->resprints .= " inner join ".$this->db->prefix()."license_orderdet lod on lo.rowid = lod.fk_license_order";
				//$this->resprints .= " inner join ".$this->db->prefix()."license_product lp on lp.rowid = lod.fk_license_product";
				//$this->resprints .= " inner join ".$this->db->prefix()."license_keylist lk on lk.rowid = lp.fk_base_key AND lk.option_code = 5";
				//$this->resprints .= " AND lo.date_creation < '2020-02-14 00:00:00'";
				//$this->resprints .= " AND s.rowid NOT IN ("; // Subquery
				//$this->resprints .= " select distinct s.rowid";
				//$this->resprints .= " from ".$this->db->prefix()."commande o";
				//$this->resprints .= " inner join ".$this->db->prefix()."societe s on s.rowid = o.fk_soc";
				//$this->resprints .= " inner join ".$this->db->prefix()."license_order lo on o.rowid = lo.fk_commande";
				//$this->resprints .= " inner join ".$this->db->prefix()."license_orderdet lod on lo.rowid = lod.fk_license_order";
				////$this->resprints .= " inner join ".$this->db->prefix()."license_product lp on lp.rowid = lod.fk_license_product";
				//$this->resprints .= " inner join ".$this->db->prefix()."license_keylist lk on lk.rowid = lp.fk_base_key AND lk.option_code = 5";
				//$this->resprints .= " where o.date_creation > '2020-02-14 00:00:00'";
				//$this->resprints .= ")";
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
		if (in_array($parameters['currentcontext'], array('orderlist'))) {
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

		if (in_array($parameters['currentcontext'], array('orderlist'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				$search_license_note = GETPOST('search_license_note', 'alpha');
				if ($search_license_note != '') {
					$this->resprints .= natural_search('lo.note', $search_license_note);
				}
				$search_identification = GETPOST('search_identification', 'alpha');
				if ($search_identification != '') {
					$this->resprints .= natural_search('lo.identification', $search_identification);
				}
				$search_status = GETPOST('search_license_status', 'int');
				if ($search_status != '') {
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

		if (in_array($parameters['currentcontext'], array('orderlist'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				dol_include_once('/licensemanager/class/licenseorder.class.php');
				$search_license_note = GETPOST('search_license_note', 'alpha');
				$this->resprints .= '<td class="liste_titre right">';
				$this->resprints .= '<input class="flat" type="text" size="4" name="search_license_note" value="'.$search_license_note.'">';
				$this->resprints .= '</td>';
				$search_identification = GETPOST('search_identification', 'alpha');
				$this->resprints .= '<td class="liste_titre right">';
				$this->resprints .= '<input class="flat" type="text" size="4" name="search_identification" value="'.$search_identification.'">';
				$this->resprints .= '</td>';
				$this->resprints .= '<td class="liste_titre right">';
				$this->resprints .= '</td>';
				$search_status = GETPOST('search_license_status', 'alpha');
				$this->resprints .= '<td class="liste_titre right">';
				$liststatus = array(
					Licenseorder::STATUS_DRAFT => $langs->trans("Draft"),
					Licenseorder::STATUS_VALIDATED => $langs->trans("Validated"),
					Licenseorder::STATUS_CANCELED => $langs->trans("Cancelled")
				);
				$this->resprints .= $form->selectarray('search_license_status', $liststatus, $search_status, -3, 0, 0, '', 0, 0, 0, '', 'search_status width100 onrightofpage');
				$this->resprints .= '</td>';
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
		global $conf, $user, $langs;

		$error = 0; // Error counter
		$this->resprints = '';

		if (in_array($parameters['currentcontext'], array('orderlist'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				$langs->load('licensemanager@licensemanager');
				$sortfield = $parameters['sortfield'];
				$sortorder = $parameters['sortorder'];
				$param = $parameters['param'];
				$this->resprints .= getTitleFieldOfList($langs->trans('LicenseNote'), 0, $_SERVER["PHP_SELF"], 'license_note', '', $param, '', $sortfield, $sortorder, 'right ');
				$this->resprints .= getTitleFieldOfList($langs->trans('LicenseIdentification'), 0, $_SERVER["PHP_SELF"], 'identification', '', $param, '', $sortfield, $sortorder, 'right ');
				$this->resprints .= getTitleFieldOfList($langs->trans('DateValid'), 0, $_SERVER["PHP_SELF"], 'license_date_valid', '', $param, '', $sortfield, $sortorder, 'right ');
				$this->resprints .= getTitleFieldOfList($langs->trans('LicenseStatus'), 0, $_SERVER["PHP_SELF"], 'license_status', '', $param, '', $sortfield, $sortorder, 'right ');
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
		global $langs, $user;

		$error = 0; // Error counter
		$this->resprints = '';

		if (in_array($parameters['currentcontext'], array('orderlist'))) {
			if ($user->rights->licensemanager->licensemanager->read) {
				dol_include_once('/licensemanager/class/licenseorder.class.php');
				$obj = $parameters['obj'];
				$this->resprints .= '<td class="right">'.$obj->license_note.'</td>';
				$this->resprints .= '<td class="right">'.$obj->identification.'</td>';
				$this->resprints .= '<td class="right">';
				if (!empty($obj->license_date_valid)) {
					$date_valid = $this->db->jdate($obj->license_date_valid);
					if ($date_valid < dol_now() && !preg_match('/(debug)|(free)|(demo)|(test)/i', $obj->license_note) && !preg_match('/(debug)|(free)|(demo)|(test)/i', $obj->identification)) {
						$this->resprints .= img_picto($langs->trans('Expired'), 'warning', '', 0);
					}
					$this->resprints .= dol_print_date($date_valid);
				}
				$this->resprints .= '</td>';
				$licenseOrder = new Licenseorder($this->db);
				$this->resprints .= '<td class="right">'.$licenseOrder->libStatut($obj->license_status, 2).'</td>';
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

		$head[$h][0] = 'customreports.php?objecttype='.$parameters['objecttype'].(empty($parameters['tabfamily']) ? '' : '&tabfamily='.$parameters['tabfamily']);
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

				$parameters['head'][$counter][0] = dol_buildpath('/licensemanager/licensemanager_tab.php', 1) . '?id=' . $id . '&amp;module='.$element;
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
