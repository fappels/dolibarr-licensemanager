<?php
/* Copyright (C) 2017       Laurent Destailleur      <eldy@users.sourceforge.net>
 * Copyright (C) 2023-2024  Frédéric France          <frederic.france@free.fr>
 * Copyright (C) 2024		MDW                      <mdeweerd@users.noreply.github.com>
 * Copyright (C) 2025 Francis Appels <francis.appels@z-application.com>
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
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file        class/licenseorder.class.php
 * \ingroup     licensemanager
 * \brief       This file is a CRUD class file for Licenseorder (Create/Read/Update/Delete)
 */

// Put here all includes required by your class file
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
/**
 * Class for Licenseorder
 */
class Licenseorder extends CommonObject
{
	/**
	 * @var string 	ID of module.
	 */
	public $module = 'licensemanager';

	/**
	 * @var string 	ID to identify managed object.
	 */
	public $element = 'licenseorder';

	/**
	 * @var string 	Name of table without prefix where object is stored. This is also the key used for extrafields management (so extrafields know the link to the parent table).
	 */
	public $table_element = 'license_order';

	/**
	 * @var string 	If permission must be checkec with hasRight('licensemanager', 'read') and not hasright('mymodyle', 'licenseorder', 'read'), you can uncomment this line
	 */
	public $element_for_permission = 'licensemanager';
	/** @var array array with multiple records */
	public $dataset = array();
	/**
	 * @var string 	String with name of icon for licenseorder. Must be a 'fa-xxx' fontawesome code (or 'fa-xxx_fa_color_size') or 'licenseorder@licensemanager' if picto is file 'img/object_licenseorder.png'.
	 */
	public $picto = 'fa-key';


	const STATUS_DRAFT = 0;
	const STATUS_VALIDATED = 1;
	const STATUS_CANCELED = 9;

	/**
	 *  'type' field format:
	 *  	'integer', 'integer:ObjectClass:PathToClass[:AddCreateButtonOrNot[:Filter[:Sortfield]]]',
	 *  	'select' (list of values are in 'options'. for integer list of values are in 'arrayofkeyval'),
	 *  	'sellist:TableName:LabelFieldName[:KeyFieldName[:KeyFieldParent[:Filter[:CategoryIdType[:CategoryIdList[:SortField]]]]]]',
	 *  	'chkbxlst:...',
	 *  	'varchar(x)',
	 *  	'text', 'text:none', 'html',
	 *   	'double(24,8)', 'real', 'price', 'stock',
	 *  	'date', 'datetime', 'timestamp', 'duration',
	 *  	'boolean', 'checkbox', 'radio', 'array',
	 *  	'mail', 'phone', 'url', 'password', 'ip'
	 *		Note: Filter must be a Dolibarr Universal Filter syntax string. Example: "(t.ref:like:'SO-%') or (t.date_creation:<:'20160101') or (t.status:!=:0) or (t.nature:is:NULL)"
	 *  'label' the translation key.
	 *  'alias' the alias used into some old hard coded SQL requests
	 *  'picto' is code of a picto to show before value in forms
	 *  'enabled' is a condition when the field must be managed (Example: 1 or 'getDolGlobalInt("MY_SETUP_PARAM")' or 'isModEnabled("multicurrency")' ...)
	 *  'position' is the sort order of field.
	 *  'notnull' is set to 1 if not null in database. Set to -1 if we must set data to null if empty ('' or 0).
	 *  'visible' says if field is visible in list (Examples: 0=Not visible, 1=Visible on list and create/update/view forms, 2=Visible on list only, 3=Visible on create/update/view form only (not list), 4=Visible on list and update/view form only (not create). 5=Visible on list and view only (not create/not update). Using a negative value means field is not shown by default on list but can be selected for viewing)
	 *  'noteditable' says if field is not editable (1 or 0)
	 *  'alwayseditable' says if field can be modified also when status is not draft ('1' or '0')
	 *  'default' is a default value for creation (can still be overwrote by the Setup of Default Values if field is editable in creation form). Note: If default is set to '(PROV)' and field is 'ref', the default value will be set to '(PROVid)' where id is rowid when a new record is created.
	 *  'index' if we want an index in database.
	 *  'foreignkey'=>'tablename.field' if the field is a foreign key (it is recommended to name the field fk_...).
	 *  'searchall' is 1 if we want to search in this field when making a search from the quick search button.
	 *  'isameasure' must be set to 1 or 2 if field can be used for measure. Field type must be summable like integer or double(24,8). Use 1 in most cases, or 2 if you don't want to see the column total into list (for example for percentage)
	 *  'css' and 'cssview' and 'csslist' is the CSS style to use on field. 'css' is used in creation and update. 'cssview' is used in view mode. 'csslist' is used for columns in lists. For example: 'css'=>'minwidth300 maxwidth500 widthcentpercentminusx', 'cssview'=>'wordbreak', 'csslist'=>'tdoverflowmax200'
	 *  'help' and 'helplist' is a 'TranslationString' to use to show a tooltip on field. You can also use 'TranslationString:keyfortooltiponlick' for a tooltip on click.
	 *  'showoncombobox' if value of the field must be visible into the label of the combobox that list record
	 *  'disabled' is 1 if we want to have the field locked by a 'disabled' attribute. In most cases, this is never set into the definition of $fields into class, but is set dynamically by some part of code.
	 *  'arrayofkeyval' to set a list of values if type is a list of predefined values. For example: array("0"=>"Draft","1"=>"Active","-1"=>"Cancel"). Note that type can be 'integer' or 'varchar'
	 *  'autofocusoncreate' to have field having the focus on a create form. Only 1 field should have this property set to 1.
	 *  'comment' is not used. You can store here any text of your choice. It is not used by application.
	 *	'validate' is 1 if you need to validate the field with $this->validateField(). Need MAIN_ACTIVATE_VALIDATION_RESULT.
	 *  'copytoclipboard' is 1 or 2 to allow to add a picto to copy value into clipboard (1=picto after label, 2=picto after value)
	 *
	 *  Note: To have value dynamic, you can set value to 0 in definition and edit the value on the fly into the constructor.
	 */

	// BEGIN MODULEBUILDER PROPERTIES
	public $fields=array(
		'rowid'=>array('type'=>'int', 'label'=>'TechnicalID', 'enabled'=>1, 'visible'=>-1, 'notnull'=>1, 'position'=>10),
		'tms'=>array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-1, 'position'=>15),
		'note'=>array('type'=>'varchar(255)', 'label'=>'Note', 'enabled'=>1, 'visible'=>1, 'position'=>20),
		'fk_user_author'=>array('type'=>'integer:User:user/class/user.class.php', 'label'=>'Fkuserauthor', 'enabled'=>1, 'visible'=>-1, 'position'=>25, 'css'=>'maxwidth500 widthcentpercentminusxx', 'csslist'=>'tdoverflowmax150'),
		'identification'=>array('type'=>'varchar(50)', 'label'=>'Identification', 'enabled'=>1, 'visible'=>1, 'position'=>30),
		'output_mode'=>array('type'=>'varchar(45)', 'label'=>'Outputmode', 'enabled'=>1, 'visible'=>1, 'position'=>35),
		'key_mode'=>array('type'=>'varchar(45)', 'label'=>'Keymode', 'enabled'=>1, 'visible'=>1, 'position'=>40),
		'fk_customer'=>array('type'=>'int', 'label'=>'Fkcustomer', 'enabled'=>1, 'visible'=>-1, 'notnull'=>1, 'position'=>45, 'css'=>'maxwidth500 widthcentpercentminusxx'),
		'fk_commande'=>array('type'=>'int', 'label'=>'Fkcommande', 'enabled'=>1, 'visible'=>-1, 'notnull'=>1, 'position'=>50, 'css'=>'maxwidth500 widthcentpercentminusxx'),
		'qty_seq_id'=>array('type'=>'int', 'label'=>'Qtyseqid', 'enabled'=>1, 'visible'=>-1, 'notnull'=>1, 'position'=>55),
		'date_creation'=>array('type'=>'date', 'label'=>'Datecreation', 'enabled'=>1, 'visible'=>1, 'position'=>60),
		'date_valid'=>array('type'=>'date', 'label'=>'DateValidation', 'enabled'=>1, 'visible'=>1, 'position'=>65),
		'status' => array('type'=>'integer', 'label'=>'Status', 'enabled'=>'1', 'position'=>2000, 'notnull'=>1, "visible"=>"5", "index"=>"1", "arrayofkeyval"=>array("0" => "Draft", "1" => "Validated", "9" => "Canceled"), "validate"=>"1",),
	);
// END MODULEBUILDER PROPERTIES

	public $note;
	public $fk_user_author;
	public $identification;
	public $output_mode;
	public $key_mode;
	public $fk_customer;
	public $fk_commande;
	public $qty_seq_id;
	public $date_creation;
	public $date_valid;
	public $status;

	// If this object has a subtable with lines

	/**
	 * @var string    Name of subtable line
	 */
	public $table_element_line = 'license_orderdet';

	/**
	 * @var string    Field with ID of parent key if this object has a parent
	 */
	public $fk_element = 'fk_license_order';

	/**
	 * @var string    Name of subtable class that manage subtable lines
	 */
	public $class_element_line = 'Licenseorderdet';

	// /**
	//  * @var array	List of child tables. To test if we can delete object.
	//  */
	// protected $childtables = array('mychildtable' => array('name'=>'Licenseorder', 'fk_element'=>'fk_licenseorder'));

	// /**
	//  * @var array    List of child tables. To know object to delete on cascade.
	//  *               If name matches '@ClassNAme:FilePathClass;ParentFkFieldName' it will
	//  *               call method deleteByParentField(parentId, ParentFkFieldName) to fetch and delete child object
	//  */
	// protected $childtablesoncascade = array('licensemanager_licenseorderdet');

	// /**
	//  * @var LicenseorderLine[]     Array of subtable lines
	//  */
	// public $lines = array();



	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		global $langs;

		$this->db = $db;
		$this->ismultientitymanaged = 0;
		$this->isextrafieldmanaged = 0;

		if (!getDolGlobalInt('MAIN_SHOW_TECHNICAL_ID') && isset($this->fields['rowid']) && !empty($this->fields['ref'])) {
			$this->fields['rowid']['visible'] = 0;
		}
		if (!isModEnabled('multicompany') && isset($this->fields['entity'])) {
			$this->fields['entity']['enabled'] = 0;
		}

		// Example to show how to set values of fields definition dynamically
		/*if ($user->hasRight('licensemanager', 'licenseorder', 'read')) {
			$this->fields['myfield']['visible'] = 1;
			$this->fields['myfield']['noteditable'] = 0;
		}*/

		// Unset fields that are disabled
		foreach ($this->fields as $key => $val) {
			if (isset($val['enabled']) && empty($val['enabled'])) {
				unset($this->fields[$key]);
			}
		}

		// Translate some data of arrayofkeyval
		if (is_object($langs)) {
			foreach ($this->fields as $key => $val) {
				if (!empty($val['arrayofkeyval']) && is_array($val['arrayofkeyval'])) {
					foreach ($val['arrayofkeyval'] as $key2 => $val2) {
						$this->fields[$key]['arrayofkeyval'][$key2] = $langs->trans($val2);
					}
				}
			}
		}
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  int 	$notrigger 0=launch triggers after, 1=disable triggers
	 * @return int             Return integer <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = 0)
	{
		$resultcreate = $this->createCommon($user, $notrigger);

		//$resultvalidate = $this->validate($user, $notrigger);

		return $resultcreate;
	}

	/**
	 *  Load object in memory from the database
	 *
	 *  @param	int		$id    Id object
	 *  @param	int		$fk_commande    Id related order
	 *  @param  int		$qty_seq_id		qty sequence number
	 *  @return int          	<0 if KO, >0 if OK
	 */
	public function fetch($id, $fk_commande, $qty_seq_id)
	{
		$moreWhere = '';
		if (($id == 0)&&($fk_commande > 0)) {
				$moreWhere .= " AND t.fk_commande = ".$fk_commande;
		}
		if (($fk_commande == 0) && ($qty_seq_id > 0)) {
			$moreWhere .= " AND t.qty_seq_id = ".$qty_seq_id;
		} elseif (($fk_commande > 0) && ($qty_seq_id > 0)) {
			$moreWhere.= " AND t.qty_seq_id = ".$qty_seq_id;
		}
		$result = $this->fetchCommon($id, '', $moreWhere);

		return $result;
	}

	/**
	 *  Load object in memory from the database
	 *
	 *  @param	string		$filter     where clause string
	 *  @param	string		$orderBy    order by string
	 *  @return int          	<0 if KO, >0 if OK
	 */
	public function fetchList($filter = '',$orderBy = '')
	{
		global $langs;

		$sql = "SELECT";
		$sql.= " t.rowid,";

		$sql.= " t.tms,";
		$sql.= " t.fk_customer,";
		$sql.= " t.fk_commande,";
		$sql.= " t.note,";
		$sql.= " t.output_mode,";
		$sql.= " t.key_mode,";
		$sql.= " t.qty_seq_id,";
		$sql.= " t.fk_user_author,";
		$sql.= " t.identification,";
		$sql.= " t.status";

		$sql.= " FROM ".MAIN_DB_PREFIX."license_order as t";
		if (!empty($filter)) {
			$sql.= " WHERE ".$filter;
		}
		if (!empty($orderBy)) {
			$sql.= " ORDER BY ".$orderBy;
		}

		dol_syslog(get_class($this)."::fetchList sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);
			$row = 0;
			$this->dataset=null;
			while ($row < $num)
			{
				$obj = $this->db->fetch_object($resql);
				$this->dataset[$row]['rowid'] = $obj->rowid;

				$this->dataset[$row]['tms'] = $this->db->jdate($obj->tms);
				$this->dataset[$row]['fk_customer'] = $obj->fk_customer;
				$this->dataset[$row]['fk_commande'] = $obj->fk_commande;
				$this->dataset[$row]['note'] = $obj->note;
				$this->dataset[$row]['output_mode'] = $obj->output_mode;
				$this->dataset[$row]['key_mode'] = $obj->key_mode;
				$this->dataset[$row]['qty_seq_id'] = $obj->qty_seq_id;
				$this->dataset[$row]['fk_user_author'] = $obj->fk_user_author;
				$this->dataset[$row]['identification'] = $obj->identification;
				$this->dataset[$row]['status'] = $obj->status;
				$row++;
			}
			$this->db->free($resql);

			return $row;
		} else {
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  int 	$notrigger 0=launch triggers after, 1=disable triggers
	 * @return int             Return integer <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = 0)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       	User that deletes
	 * @param int 	$notrigger  0=launch triggers, 1=disable triggers
	 * @return int             	Return integer <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = 0)
	{
		return $this->deleteCommon($user, $notrigger);
		//return $this->deleteCommon($user, $notrigger, 1);
	}

	/**
	 * Load object lines in memory from the database
	 *
	 * @param	int		$noextrafields	0=Default to load extrafields, 1=No extrafields
	 * @return 	int         			Return integer <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchLines($noextrafields = 0)
	{
		$this->lines = array();

		$result = $this->fetchLinesCommon('', $noextrafields);
		return $result;
	}

	/**
	 * function add licenses for products with licenses assigned
	 *
	 * @param object $licenseKeylist $license source object
	 * @param int $fk_license_product related product id
	 * @param int $fk_commande_det related order line
	 *
	 * @return int create result NOK < 0 OK > 0
	 */
	public function addLine($user, $licenseKeylist, $fk_license_product, $fk_commande_det)
	{
		$now=dol_now();
		$licenseOrderDet = new Licenseorderdet($this->db);
		$licenseOrderDet->fk_license_order = $this->id;
		$licenseOrderDet->fk_license_product = $fk_license_product;
		$licenseOrderDet->fk_commande_det = $fk_commande_det;
		$licenseOrderDet->datec = $now;
		$licenseOrderDet->datev = dol_time_plus_duree($now,$licenseKeylist->duration,$licenseKeylist->duration_unit);
		$result = $licenseOrderDet->create($user);
		if ($result > 0 && empty($this->date_valid)) {
			$this->date_valid = $licenseOrderDet->datev;
			$this->update($user);
		}
		return $result;
	}

	/**
	 *  Delete a line of object in database
	 *
	 *	@param  User	$user       User that delete
	 *  @param	int		$idline		Id of line to delete
	 *  @param 	int 	$notrigger  0=launch triggers after, 1=disable triggers
	 *  @return int         		>0 if OK, <0 if KO
	 */
	public function deleteLine(User $user, $idline, $notrigger = 0)
	{
		if ($this->status < 0) {
			$this->error = 'ErrorDeleteLineNotAllowedByObjectStatus';
			return -2;
		}

		return $this->deleteLineCommon($user, $idline, $notrigger);
	}

	/**
	 * function to generate and store the key for product license
	 *
	 * @param User $user User who generates key
	 * @param array $data assosiative array with license order detail data
	 * @return string|int generated key or < 0 if error
	 */

	public function generate($user, $data)
	{
		$licenseProduct = new Licenseproduct($this->db);
		if ($licenseProduct->fetch($data['fk_license_product'], 0) > 0) {
			$licenseKeylist = new Licensekeylist($this->db);
			if ($licenseKeylist->fetch($licenseProduct->fk_base_key) > 0) {
				$licensenOrderDet = new Licenseorderdet($this->db);
				if ($licensenOrderDet->fetch($data["rowid"]) > 0) {
					if ($licenseKeylist->type == 0) {
						$hashData = array($this->identification, $licenseProduct->option_code, $licenseKeylist->option_code);
						$licensenOrderDet->license_key = $licenseKeylist->generate($hashData);
						if ($licensenOrderDet->update($user) > 0) {
							return $licensenOrderDet->license_key;
						}
					} else {
						$licenseList = new Licenselist($this->db);
						if ($licenseList->fetchNext($licenseKeylist->id) > 0) {
							$licensenOrderDet->license_key = $licenseList->external_key;
							if ($licensenOrderDet->update($user) > 0) {
								return $licensenOrderDet->license_key;
							}
						}
					}
				} else {
					$this->error = 'ErrorLicenseOrderDetNotFound';
				}
			} else {
				$this->error = 'ErrorLicenseKeyNotFound';
			}
		} else {
			$this->error = 'ErrorLicenseProductNotFound';
		}
		return -1;
	}

	/**
	 * function to renew expired date for product license
	 *
	 * @param User $user User who renews key
	 * @param array $data assosiative array with license order detail data
	 * @return string|int > 0 if renewed or < 0 if error
	 */

	 public function renew($user, $data)
	 {
		$now = dol_now();
		$licenseProduct = new Licenseproduct($this->db);
		if ($licenseProduct->fetch($data['fk_license_product'], 0) > 0) {
			$licenseKeylist = new Licensekeylist($this->db);
			if ($licenseKeylist->fetch($licenseProduct->fk_base_key) > 0) {
				$licenseOrderDet = new Licenseorderdet($this->db);
				if ($licenseOrderDet->fetch($data["rowid"]) > 0) {
					$licenseOrderDet->datev = dol_time_plus_duree($now,$licenseKeylist->duration,$licenseKeylist->duration_unit);
					if ($licenseKeylist->type == 0) {
						if ($licenseOrderDet->update($user) > 0) {
							return $licenseOrderDet->license_key;
						}
					} else {
						$licenseList = new Licenselist($this->db);
						if ($licenseList->fetchNext($licenseKeylist->id) > 0) {
							$licenseOrderDet->license_key = $licenseList->external_key;
							if ($licenseOrderDet->update($user) > 0) {
								return $licenseOrderDet->license_key;
							}
						}
					}
				 } else {
					$this->error = 'ErrorLicenseOrderDetNotFound';
				 }
			 } else {
				 $this->error = 'ErrorLicenseKeyNotFound';
			 }
		 } else {
			 $this->error = 'ErrorLicenseProductNotFound';
		 }
		 return -1;
	 }

	/**
	 * print license list
	 *
	 * @param object $form form where list is part of
	 * @param string $data licenseorderdet data
	 * @param License &$multiLicense multiLicense reference to append multi licenses into
	 * @param Licenseorder $order order object
	 * @param Licenseorder|null $otherOrder other order object to use for cancel button
	 *
	 * @return void
	 */
	public function licenseOrderDetList($form, $data, &$multiLicense, $order, $otherOrder = null)
	{
		global $langs;

		$licenseProduct = new Licenseproduct($this->db);
		$licenseKeylist = new Licensekeylist($this->db);
		$prod= new Product($this->db);
		$keyTypes = $licenseKeylist->getKeyTypes();
		$license = new License();

		if ($licenseProduct->fetch($data['fk_license_product'])> 0) {
			if ($licenseKeylist->fetch($licenseProduct->fk_base_key) > 0) {
				//product
				$prod->fetch($licenseProduct->fk_product);
				print '<td align="center">'.$form->textwithtooltip($prod->ref,$prod->label,1).'</td>';
				//LicenseType
				print '<td align="center">'.$keyTypes[$licenseKeylist->type].'</td>';
				//Licensename
				print '<td align="center">'.$licenseKeylist->name.'</td>';
				//Date create
				print '<td align="center">'.dol_print_date($data['datec'],'daytext').'</td>';
				//Date expire
				print '<td align="center">'.dol_print_date($data['datev'],'daytext').'</td>';
				//status
				print '<td align="center">'.$this->getLibStatut(2).'</td>';
				// print Licensekey when it is a single license else print 'multi'
				$buttonEnabled = 0;
				if ($multiLicense->key_mode == 'multi')	{
					if ($this->status == self::STATUS_VALIDATED) $buttonEnabled = 1;
					if ($multiLicense->code) $multiLicense->code .= $licenseKeylist->multi_key_separator;
					$multiLicense->code .= $data['license_key'];
					if ($data['license_key'] != '')	{
						print '<td align="center">'.$langs->trans('multi').'</td>';
					}
				} else {
					$license->key_mode = $this->key_mode;
					$license->code = $data['license_key'];
					$license->output_mode = $this->output_mode;

					print $this->htmlLicense($license);
				}

				if ($otherOrder) {
					$orderRef = $otherOrder->ref;
				} else {
					$orderRef = $order->ref;
				}

				if ($orderRef != $order->ref) {
					// print button to cancel other license
					print '<td align="center">'.dolGetButtonAction('', $langs->trans('CancelLicense', $orderRef), 'danger', $_SERVER["PHP_SELF"] . '?licenseid=' . $this->id . '&amp;id='.  $order->id .'&amp;action=cancel_license&token='.newToken(), '', $buttonEnabled).'</td>';
				}
			}
		}
	}

	/**
	 * print license key
	 *
	 * @param object $license license containing output mode and code
	 *
	 * @return string html output of license key
	 */

	public function htmlLicense($license) {
		if (!$license->output_mode ||($license->output_mode == 'text')) {
			return '<td align="center"><span padding="10%">'.$license->code.'</span></td>';
		} elseif (Licensekeylist::is2d($license->output_mode)) {
			require_once TCPDF_PATH.'tcpdf_barcodes_2d.php';
			$barcodeobj = new TCPDF2DBarcode($license->code, $license->output_mode);
			return '<td align="center"><span padding="10%">'.$barcodeobj->getBarcodeHTML(2,2).'</span></td>';
		} else {
			require_once TCPDF_PATH.'tcpdf_barcodes_1d.php';
			$barcodeobj = new TCPDFBarcode($license->code, $license->output_mode);
			return '<td align="center"><span padding="10%">'.$barcodeobj->getBarcodeHTML().'</span></td>';
		}
	}

	/**
	 *	Validate object
	 *
	 *	@param		User	$user     		User making status change
	 *  @param		int		$notrigger		1=Does not execute triggers, 0= execute triggers
	 *	@return  	int						Return integer <=0 if OK, 0=Nothing done, >0 if KO
	 */
	public function validate($user, $notrigger = 0)
	{
		global $conf;

		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

		$error = 0;

		// Protection
		if ($this->status == self::STATUS_VALIDATED) {
			dol_syslog(get_class($this)."::validate action abandoned: already validated", LOG_WARNING);
			return 0;
		}

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'LICENSEMANAGER_LICENSEORDER_VALIDATE');
	}


	/**
	 *	Set draft status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						Return integer <0 if KO, >0 if OK
	 */
	public function setDraft($user, $notrigger = 0)
	{
		// Protection
		if ($this->status <= self::STATUS_DRAFT) {
			return 0;
		}

		/* if (! ((!getDolGlobalInt('MAIN_USE_ADVANCED_PERMS') && $user->hasRight('licensemanager','write'))
		 || (getDolGlobalInt('MAIN_USE_ADVANCED_PERMS') && $user->hasRight('licensemanager','licensemanager_advance','validate'))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_DRAFT, $notrigger, 'LICENSEMANAGER_LICENSEORDER_UNVALIDATE');
	}

	/**
	 *	Set cancel status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						Return integer <0 if KO, 0=Nothing done, >0 if OK
	 */
	public function cancel($user, $notrigger = 0)
	{
		// Protection
		if ($this->status != self::STATUS_VALIDATED) {
			return 0;
		}

		/* if (! ((!getDolGlobalInt('MAIN_USE_ADVANCED_PERMS') && $user->hasRight('licensemanager','write'))
		 || (getDolGlobalInt('MAIN_USE_ADVANCED_PERMS') && $user->hasRight('licensemanager','licensemanager_advance','validate'))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_CANCELED, $notrigger, 'LICENSEMANAGER_LICENSEORDER_CANCEL');
	}

	/**
	 *	Set back to validated status
	 *
	 *	@param	User	$user			Object user that modify
	 *  @param	int		$notrigger		1=Does not execute triggers, 0=Execute triggers
	 *	@return	int						Return integer <0 if KO, 0=Nothing done, >0 if OK
	 */
	public function reopen($user, $notrigger = 0)
	{
		// Protection
		if ($this->status == self::STATUS_VALIDATED) {
			return 0;
		}

		/*if (! ((!getDolGlobalInt('MAIN_USE_ADVANCED_PERMS') && $user->hasRight('licensemanager','write'))
		 || (getDolGlobalInt('MAIN_USE_ADVANCED_PERMS') && $user->hasRight('licensemanager','licensemanager_advance','validate'))))
		 {
		 $this->error='Permission denied';
		 return -1;
		 }*/

		return $this->setStatusCommon($user, self::STATUS_VALIDATED, $notrigger, 'LICENSEMANAGER_LICENSEORDER_REOPEN');
	}

	/**
	 * getTooltipContentArray
	 *
	 * @param 	array 	$params 	Params to construct tooltip data
	 * @since 	v18
	 * @return 	array
	 */
	public function getTooltipContentArray($params)
	{
		global $langs;

		$datas = [];

		if (getDolGlobalInt('MAIN_OPTIMIZEFORTEXTBROWSER')) {
			return ['optimize' => $langs->trans("ShowLicenseorder")];
		}
		$datas['picto'] = img_picto('', $this->picto).' <u>'.$langs->trans("Licenseorder").'</u>';
		if (isset($this->status)) {
			$datas['picto'] .= ' '.$this->getLibStatut(5);
		}
		if (property_exists($this, 'ref')) {
			$datas['ref'] = '<br><b>'.$langs->trans('Ref').':</b> '.$this->ref;
		}
		if (property_exists($this, 'label')) {
			$datas['ref'] = '<br>'.$langs->trans('Label').':</b> '.$this->label;
		}

		return $datas;
	}

	/**
	 *  Return a link to the object card (with optionally the picto)
	 *
	 *  @param  int     $withpicto                  Include picto in link (0=No picto, 1=Include picto into link, 2=Only picto)
	 *  @param  string  $option                     On what the link point to ('nolink', ...)
	 *  @param  int     $notooltip                  1=Disable tooltip
	 *  @param  string  $morecss                    Add more css on link
	 *  @param  int     $save_lastsearch_value      -1=Auto, 0=No save of lastsearch_values when clicking, 1=Save lastsearch_values whenclicking
	 *  @return	string                              String with URL
	 */
	public function getNomUrl($withpicto = 0, $option = '', $notooltip = 0, $morecss = '', $save_lastsearch_value = -1)
	{
		global $conf, $langs, $hookmanager;

		if (!empty($conf->dol_no_mouse_hover)) {
			$notooltip = 1; // Force disable tooltips
		}

		$result = '';
		$params = [
			'id' => $this->id,
			'objecttype' => $this->element.($this->module ? '@'.$this->module : ''),
			'option' => $option,
		];
		$classfortooltip = 'classfortooltip';
		$dataparams = '';
		if (getDolGlobalInt('MAIN_ENABLE_AJAX_TOOLTIP')) {
			$classfortooltip = 'classforajaxtooltip';
			$dataparams = ' data-params="'.dol_escape_htmltag(json_encode($params)).'"';
			$label = '';
		} else {
			$label = implode($this->getTooltipContentArray($params));
		}

		$url = dol_buildpath('/licensemanager/licenseorder_card.php', 1).'?id='.$this->id;

		if ($option !== 'nolink') {
			// Add param to save lastsearch_values or not
			$add_save_lastsearch_values = ($save_lastsearch_value == 1 ? 1 : 0);
			if ($save_lastsearch_value == -1 && isset($_SERVER["PHP_SELF"]) && preg_match('/list\.php/', $_SERVER["PHP_SELF"])) {
				$add_save_lastsearch_values = 1;
			}
			if ($url && $add_save_lastsearch_values) {
				$url .= '&save_lastsearch_values=1';
			}
		}

		$linkclose = '';
		if (empty($notooltip)) {
			if (getDolGlobalInt('MAIN_OPTIMIZEFORTEXTBROWSER')) {
				$label = $langs->trans("ShowLicenseorder");
				$linkclose .= ' alt="'.dol_escape_htmltag($label, 1).'"';
			}
			$linkclose .= ($label ? ' title="'.dol_escape_htmltag($label, 1).'"' : ' title="tocomplete"');
			$linkclose .= $dataparams.' class="'.$classfortooltip.($morecss ? ' '.$morecss : '').'"';
		} else {
			$linkclose = ($morecss ? ' class="'.$morecss.'"' : '');
		}

		if ($option == 'nolink' || empty($url)) {
			$linkstart = '<span';
		} else {
			$linkstart = '<a href="'.$url.'"';
		}
		$linkstart .= $linkclose.'>';
		if ($option == 'nolink' || empty($url)) {
			$linkend = '</span>';
		} else {
			$linkend = '</a>';
		}

		$result .= $linkstart;

		if (empty($this->showphoto_on_popup)) {
			if ($withpicto) {
				$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), (($withpicto != 2) ? 'class="paddingright"' : ''), 0, 0, $notooltip ? 0 : 1);
			}
		} else {
			if ($withpicto) {
				require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

				list($class, $module) = explode('@', $this->picto);
				$upload_dir = $conf->$module->multidir_output[$conf->entity]."/$class/".dol_sanitizeFileName($this->ref);
				$filearray = dol_dir_list($upload_dir, "files");
				$filename = $filearray[0]['name'];
				if (!empty($filename)) {
					$pospoint = strpos($filearray[0]['name'], '.');

					$pathtophoto = $class.'/'.$this->ref.'/thumbs/'.substr($filename, 0, $pospoint).'_mini'.substr($filename, $pospoint);
					if (!getDolGlobalString(strtoupper($module.'_'.$class).'_FORMATLISTPHOTOSASUSERS')) {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><div class="photoref"><img class="photo'.$module.'" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div></div>';
					} else {
						$result .= '<div class="floatleft inline-block valignmiddle divphotoref"><img class="photouserphoto userphoto" alt="No photo" border="0" src="'.DOL_URL_ROOT.'/viewimage.php?modulepart='.$module.'&entity='.$conf->entity.'&file='.urlencode($pathtophoto).'"></div>';
					}

					$result .= '</div>';
				} else {
					$result .= img_object(($notooltip ? '' : $label), ($this->picto ? $this->picto : 'generic'), ($notooltip ? (($withpicto != 2) ? 'class="paddingright"' : '') : 'class="'.(($withpicto != 2) ? 'paddingright ' : '').'"'), 0, 0, $notooltip ? 0 : 1);
				}
			}
		}

		if ($withpicto != 2) {
			$result .= $this->ref;
		}

		$result .= $linkend;
		//if ($withpicto != 2) $result.=(($addlabel && $this->label) ? $sep . dol_trunc($this->label, ($addlabel > 1 ? $addlabel : 0)) : '');

		global $action, $hookmanager;
		$hookmanager->initHooks(array($this->element.'dao'));
		$parameters = array('id' => $this->id, 'getnomurl' => &$result);
		$reshook = $hookmanager->executeHooks('getNomUrl', $parameters, $this, $action); // Note that $action and $object may have been modified by some hooks
		if ($reshook > 0) {
			$result = $hookmanager->resPrint;
		} else {
			$result .= $hookmanager->resPrint;
		}

		return $result;
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLabelStatus($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	/**
	 *  Return the label of the status
	 *
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return	string 			       Label of status
	 */
	public function getLibStatut($mode = 0)
	{
		return $this->LibStatut($this->status, $mode);
	}

	// phpcs:disable PEAR.NamingConventions.ValidFunctionName.ScopeNotCamelCaps
	/**
	 *  Return the label of a given status
	 *
	 *  @param	int		$status        Id status
	 *  @param  int		$mode          0=long label, 1=short label, 2=Picto + short label, 3=Picto, 4=Picto + long label, 5=Short label + Picto, 6=Long label + Picto
	 *  @return string 			       Label of status
	 */
	public function LibStatut($status, $mode = 0)
	{
		// phpcs:enable
		if (is_null($status)) {
			return '';
		}

		if (empty($this->labelStatus) || empty($this->labelStatusShort)) {
			global $langs;
			//$langs->load("licensemanager@licensemanager");
			$this->labelStatus[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatus[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Active');
			$this->labelStatus[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Expired');
			$this->labelStatusShort[self::STATUS_DRAFT] = $langs->transnoentitiesnoconv('Draft');
			$this->labelStatusShort[self::STATUS_VALIDATED] = $langs->transnoentitiesnoconv('Active');
			$this->labelStatusShort[self::STATUS_CANCELED] = $langs->transnoentitiesnoconv('Expired');
		}

		$statusType = 'status'.$status;
		//if ($status == self::STATUS_VALIDATED) $statusType = 'status1';
		if ($status == self::STATUS_CANCELED) {
			$statusType = 'status6';
		}

		return dolGetStatus($this->labelStatus[$status], $this->labelStatusShort[$status], '', $statusType, $mode);
	}

	/**
	 *  Create a document onto disk according to template module.
	 *
	 *  @param	    string		$modele			Force template to use ('' to not force)
	 *  @param		Translate	$outputlangs	object lang a utiliser pour traduction
	 *  @param      int			$hidedetails    Hide details of lines
	 *  @param      int			$hidedesc       Hide description
	 *  @param      int			$hideref        Hide ref
	 *  @param      null|array  $moreparams     Array to provide more information
	 *  @return     int         				0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0, $moreparams = null)
	{
		global $langs;

		$result = 0;
		$includedocgeneration = 0;

		$langs->load("licensemanager@licensemanager");

		if (!dol_strlen($modele)) {
			$modele = 'standard_licenseorder';

			if (!empty($this->model_pdf)) {
				$modele = $this->model_pdf;
			} elseif (getDolGlobalString('LICENSEORDER_ADDON_PDF')) {
				$modele = getDolGlobalString('LICENSEORDER_ADDON_PDF');
			}
		}

		$modelpath = "core/modules/licensemanager/doc/";

		if ($includedocgeneration && !empty($modele)) {
			$result = $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref, $moreparams);
		}

		return $result;
	}

	/**
	 * Return validation test result for a field.
	 * Need MAIN_ACTIVATE_VALIDATION_RESULT to be called.
	 *
	 * @param  array   $fields	       		Array of properties of field to show
	 * @param  string  $fieldKey            Key of attribute
	 * @param  string  $fieldValue          value of attribute
	 * @return bool 						Return false if fail, true on success, set $this->error for error message
	 */
	public function validateField($fields, $fieldKey, $fieldValue)
	{
		// Add your own validation rules here.
		// ...

		return parent::validateField($fields, $fieldKey, $fieldValue);
	}

	/**
	 * Action executed by scheduler
	 * CAN BE A CRON TASK. In such a case, parameters come from the schedule job setup field 'Parameters'
	 * Use public function doScheduledJob($param1, $param2, ...) to get parameters
	 *
	 * @return	int			0 if OK, <>0 if KO (this function is used also by cron so only 0 is OK)
	 */
	public function doScheduledJob()
	{
		//global $conf, $langs;

		//$conf->global->SYSLOG_FILE = 'DOL_DATA_ROOT/dolibarr_mydedicatedlogfile.log';

		$error = 0;
		$this->output = '';
		$this->error = '';

		dol_syslog(__METHOD__." start", LOG_INFO);

		$now = dol_now();

		$this->db->begin();

		// ...

		$this->db->commit();

		dol_syslog(__METHOD__." end", LOG_INFO);

		return $error;
	}
}


require_once DOL_DOCUMENT_ROOT.'/core/class/commonobjectline.class.php';

/**
 * Class LicenseorderLine. You can also remove this and generate a CRUD class for lines objects.
 */
class Licenseorderdet extends CommonObjectLine
{
	// To complete with content of an object LicenseorderLine
	// We should have a field rowid, fk_licenseorder and position

	/**
	 * To overload
	 * @see CommonObjectLine
	 */
	public $parent_element = 'licenseorder';		// Example: '' or 'licenseorder'

	/**
	 * To overload
	 * @see CommonObjectLine
	 */
	public $fk_parent_attribute = 'fk_license_order';	// Example: '' or 'fk_licenseorder'

	/**
	 * @var int    Name of subtable line
	 */
	public $table_element = 'license_orderdet';

	/**
	 * @var array  Array with all fields and their property. Do not use it as a static var. It may be modified by constructor.
	 */
	public $fields=array(
		'rowid' => array('type'=>'integer', 'label'=>'TechnicalID', 'enabled'=>1, 'position'=>1, 'notnull'=>1, 'visible'=>-1, 'noteditable'=>'1', 'index'=>1, 'comment'=>"Id"),
		'tms'=>array('type'=>'timestamp', 'label'=>'DateModification', 'enabled'=>1, 'visible'=>-1, 'position'=>15),
		'fk_license_product' => array('type'=>'integer:Licenseproduct:licensemanager/class/licenseproduct.class.php', 'label'=>'LicenseProduct', 'enabled'=>1, 'visible'=>1, 'position'=>10, 'notnull'=>1, 'index'=>1,),
		'fk_license_list' => array('type'=>'integer:Licenselist:licensemanager/class/licenselist.class.php', 'label'=>'LicenseList', 'enabled'=>'1', 'notnull'=>-1, 'visible'=>1),
		'fk_license_order' => array('type'=>'integer:Licenseorder:licensemanager/class/licenseorder.class.php', 'label'=>'LicenseOrder', 'enabled'=>'1', 'notnull'=>-1, 'visible'=>1),
		'fk_commande_det' => array('type'=>'integer', 'label'=>'OrderLine', 'enabled'=>'1', 'notnull'=>-1, 'visible'=>1),
		'datec'=>array('type'=>'date', 'label'=>'Datecreation', 'enabled'=>1, 'visible'=>1, 'position'=>60),
		'datev'=>array('type'=>'date', 'label'=>'DateValid', 'enabled'=>1, 'visible'=>1, 'position'=>65),
		'license_key' => array('type'=>'VARCHAR(4096)', 'label'=>'LicenseKey', 'enabled'=>1, 'visible'=>1, 'position'=>60, 'notnull'=>-1,),
	);

	public $fk_license_product;
	public $fk_license_list;
	public $fk_license_order;
	public $fk_commande_det;
	public $datec;
	public $datev;
	public $license_key;

	// array with multiple records
	public $dataset=array();

	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct(DoliDB $db)
	{
		$this->db = $db;

		$this->isextrafieldmanaged = 0;
	}

	/**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		return $this->createCommon($user, $notrigger);
	}

	/**
	 * Load object in memory from the database
	 *
	 * @param int    $id   Id object
	 * @param string $ref  Ref
	 * @return int         <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetch($id, $ref = null)
	{
		$result = $this->fetchCommon($id, $ref);
		return $result;
	}

	/**
	 *  Load all objects in memory from database
	 *
	 *  @param	string		$filter     where clause string
	 *  @param	string		$orderBy    order by string
	 *  @return int          	<0 if KO, >0 if OK (nbr rows)
	 */
	public function fetchList($filter = '',$orderBy = '')
	{
		global $langs;
		$sql = "SELECT";
		$sql.= " t.rowid,";

		$sql.= " t.tms,";
		$sql.= " t.fk_license_product,";
		$sql.= " t.fk_license_list,";
		$sql.= " t.fk_license_order,";
		$sql.= " t.fk_commande_det,";
		$sql.= " t.datec,";
		$sql.= " t.datev,";
		$sql.= " t.license_key";


		$sql.= " FROM ".MAIN_DB_PREFIX."license_orderdet as t";
		if (!empty($filter)){
			$sql.= " WHERE ".$filter;
		}
		if (!empty($orderBy)){
			$sql.= " ORDER BY ".$orderBy;
		}

		dol_syslog(get_class($this)."::fetchList sql=".$sql, LOG_DEBUG);
		$resql=$this->db->query($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$row = 0;
			$this->dataset=null;
			while ($row < $num)
			{
				$obj = $this->db->fetch_object($resql);

				$this->dataset[$row]['rowid'] = $obj->rowid;

				$this->dataset[$row]['tms'] = $this->db->jdate($obj->tms);
				$this->dataset[$row]['fk_license_product'] = $obj->fk_license_product;
				$this->dataset[$row]['fk_license_list'] = $obj->fk_license_list;
				$this->dataset[$row]['fk_license_order'] = $obj->fk_license_order;
				$this->dataset[$row]['fk_commande_det'] = $obj->fk_commande_det;
				$this->dataset[$row]['datec'] = $this->db->jdate($obj->datec);
				$this->dataset[$row]['datev'] = $this->db->jdate($obj->datev);
				$this->dataset[$row]['license_key'] = $obj->license_key;
				$row++;
			}
			$this->db->free($resql);

			return $row;
		}
		else
		{
			$this->error="Error ".$this->db->lasterror();
			dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
			return -1;
		}
	}

	/**
	 * Update object into database
	 *
	 * @param  User $user      User that modifies
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function update(User $user, $notrigger = false)
	{
		return $this->updateCommon($user, $notrigger);
	}

	/**
	 * Delete object in database
	 *
	 * @param User $user       User that deletes
	 * @param bool $notrigger  false=launch triggers after, true=disable triggers
	 * @return int             <0 if KO, >0 if OK
	 */
	public function delete(User $user, $notrigger = false)
	{
		return $this->deleteCommon($user, $notrigger);
	}
}

/**
 *	License properties
 */
class License
{
	public $code;
	public $key_mode;
	public $output_mode;
}
