<?php
/* Copyright (C) 2025 Francis Appels <francis.appels@z-application.com>
 * Copyright (C) 2024		MDW							<mdeweerd@users.noreply.github.com>
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
 * \file    core/triggers/interface_99_modLicenseManager_LicenseManagerTriggers.class.php
 * \ingroup licensemanager
 * \brief   Example trigger.
 *
 * Put detailed description here.
 *
 * \remarks You can create other triggers by copying this one.
 * - File name should be either:
 *      - interface_99_modLicenseManager_MyTrigger.class.php
 *      - interface_99_all_MyTrigger.class.php
 * - The file must stay in core/triggers
 * - The class name must be InterfaceMytrigger
 */

require_once DOL_DOCUMENT_ROOT.'/core/triggers/dolibarrtriggers.class.php';


/**
 *  Class of triggers for LicenseManager module
 */
class InterfaceLicenseManagerTriggers extends DolibarrTriggers
{
	/**
	 * Constructor
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
		parent::__construct($db);
		$this->family = "products";
		$this->description = "LicenseManager triggers.";
		$this->version = '1.0.5';            // 'development', 'experimental', 'dolibarr' or version
		$this->picto = 'licensemanager@licensemanager';
	}

	/**
	 * Function called when a Dolibarr business event is done.
	 * All functions "runTrigger" are triggered if file
	 * is inside directory core/triggers
	 *
	 * @param string 		$action 	Event action code
	 * @param CommonObject 	$object 	Object
	 * @param User 			$user 		Object user
	 * @param Translate 	$langs 		Object langs
	 * @param Conf 			$conf 		Object conf
	 * @return int              		Return integer <0 if KO, 0 if no triggered ran, >0 if OK
	 */
	public function runTrigger($action, $object, User $user, Translate $langs, Conf $conf)
	{
		if (!isModEnabled('licensemanager')) {
			return 0; // If module is not enabled, we do nothing
		}

		// Put here code you want to execute when a Dolibarr business events occurs.
		// Data and type of action are stored into $object and $action

		// You can isolate code for each action in a separate method: this method should be named like the trigger in camelCase.
		// For example : COMPANY_CREATE => public function companyCreate($action, $object, User $user, Translate $langs, Conf $conf)
		$methodName = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($action)))));
		$callback = array($this, $methodName);
		if (is_callable($callback)) {
			dol_syslog(
				"Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id
			);

			return call_user_func($callback, $action, $object, $user, $langs, $conf);
		}

		// Or you can execute some code here
		switch ($action) {
			// Users
			//case 'USER_CREATE':
			//case 'USER_MODIFY':
			//case 'USER_NEW_PASSWORD':
			//case 'USER_ENABLEDISABLE':
			//case 'USER_DELETE':

			// Actions
			//case 'ACTION_MODIFY':
			//case 'ACTION_CREATE':
			//case 'ACTION_DELETE':

			// Groups
			//case 'USERGROUP_CREATE':
			//case 'USERGROUP_MODIFY':
			//case 'USERGROUP_DELETE':

			// Companies
			//case 'COMPANY_CREATE':
			//case 'COMPANY_MODIFY':
			//case 'COMPANY_DELETE':

			// Contacts
			//case 'CONTACT_CREATE':
			//case 'CONTACT_MODIFY':
			//case 'CONTACT_DELETE':
			//case 'CONTACT_ENABLEDISABLE':

			// Products
			//case 'PRODUCT_CREATE':
			//case 'PRODUCT_MODIFY':
			case 'PRODUCT_DELETE': {
				dol_syslog("Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id);
				dol_include_once('/licensemanager/class/licenseproduct.class.php');

				$licenseProduct = new Licenseproduct($this->db);
				if ($licenseProduct->fetch(0, $object->id) > 0) {
					if ($licenseProduct->delete($user) < 0) {
						dol_syslog("Trigger failed'" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id);
					}
				}
				break;
			}
			//case 'PRODUCT_PRICE_MODIFY':
			//case 'PRODUCT_SET_MULTILANGS':
			//case 'PRODUCT_DEL_MULTILANGS':

			//Stock movement
			//case 'STOCK_MOVEMENT':

			//MYECMDIR
			//case 'MYECMDIR_CREATE':
			//case 'MYECMDIR_MODIFY':
			//case 'MYECMDIR_DELETE':

			// Sales orders
			//case 'ORDER_CREATE':
			//case 'ORDER_MODIFY':
			//case 'ORDER_VALIDATE':
			case 'ORDER_DELETE': {
				// delete licenseorder and licenseorderdet
				require_once DOL_DOCUMENT_ROOT . "/commande/class/commande.class.php";
				dol_include_once('/licensemanager/class/licenseorder.class.php');

				dol_syslog("Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->id);
				$licenseOrderList = new Licenseorder($this->db);
				if ($licenseOrderList->fetchList("fk_commande = $object->id", '') > 0) {
					foreach ($licenseOrderList->dataset as $data) {
						$licenseOrder = new Licenseorder($this->db);
						if ($licenseOrder->fetch($data['rowid'], 0, 0) > 0) {
							$licenseOrderDetList = new Licenseorderdet($this->db);

							if ($licenseOrderDetList->fetchList("fk_license_order = $licenseOrder->id", '') > 0) {
								foreach ($licenseOrderDetList->dataset as $data) {
									$licenseOrderDet = new Licenseorderdet($this->db);
									$licenseOrderDet->id = $data['rowid'];
									if ($licenseOrderDet->delete($user) > 0) {
										dol_syslog("Trigger '" . $this->name . "' for action '$action' for licenseorder detail " . __FILE__ . ". id=" . $licenseOrderDet->id);
									}
								}
							}
							if ($licenseOrder->delete($user, 0)) {
								dol_syslog("Trigger '" . $this->name . "' for action '$action' for licenseorder " . __FILE__ . ". id=" . $licenseOrder->id);
							}
						}
					}
				}
				break;
			}
			//case 'ORDER_CANCEL':
			//case 'ORDER_SENTBYMAIL':
			//case 'ORDER_CLASSIFY_BILLED':		// TODO Replace it with ORDER_BILLED
			//case 'ORDER_CLASSIFY_UNBILLED':	// TODO Replace it with ORDER_UNBILLED
			//case 'ORDER_SETDRAFT':
			case 'LINEORDER_INSERT': {
				require_once DOL_DOCUMENT_ROOT . "/commande/class/commande.class.php";
				require_once DOL_DOCUMENT_ROOT . "/core/lib/date.lib.php";
				dol_include_once('/licensemanager/class/licensekeylist.class.php');
				dol_include_once('/licensemanager/class/licenseproduct.class.php');
				dol_include_once('/licensemanager/class/licenseorder.class.php');
				//launch
				dol_syslog("Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->rowid);
				$orderLine = new OrderLine($this->db);
				$order = new Commande($this->db);
				$orderLine->fetch($object->rowid);

				if ($order->fetch($orderLine->fk_commande) > 0) {
					$licenseProduct = new Licenseproduct($this->db);
					if ($licenseProduct->fetch(0, $orderLine->fk_product) > 0) {
						$licenseKeylist = new Licensekeylist($this->db);
						if ($licenseKeylist->fetch($licenseProduct->fk_base_key) > 0) {
							for ($i = 1; $i <= $orderLine->qty; $i++) {
								$licenseOrder = new Licenseorder($this->db);
								$result = $licenseOrder->fetch(0, $order->id, $i);
								if (($result > 0) && ($licenseOrder->id > 0) && ($licenseOrder->key_mode == $licenseKeylist->mode)
								) {
									// only add licenses of same mode to license order (single or multi)
									if ($licenseOrder->addLine($user, $licenseKeylist, $licenseProduct->id, $object->rowid) > 0) {
										dol_syslog("Trigger '" . $this->name . "' for action '$action' for product id=" . $licenseProduct->fk_product);
									}
								} else {
									$licenseOrder->fk_customer = $order->socid;
									$licenseOrder->fk_commande = $order->id;
									$licenseOrder->fk_user_author = $user->id;
									// and copy mode and output_mode to order
									$licenseOrder->output_mode = $licenseKeylist->output_mode;
									$licenseOrder->key_mode = $licenseKeylist->mode;
									$licenseOrder->qty_seq_id = $i;
									if ($licenseOrder->create($user) > 0) {
										if ($licenseOrder->addLine($user, $licenseKeylist, $licenseProduct->id, $object->rowid) > 0) {
											dol_syslog("Trigger '" . $this->name . "' for action '$action' for product id=" . $licenseProduct->fk_product);
										}
									}
								}
							}
						}
					}
				}
				break;
			}
			//case 'LINEORDER_MODIFY':
			case 'LINEORDER_DELETE': {
				// delete licenseorderdet
				dol_include_once('/licensemanager/class/licenseorder.class.php');

				dol_syslog("Trigger '" . $this->name . "' for action '$action' launched by " . __FILE__ . ". id=" . $object->rowid);
				$licenseOrderDetList = new Licenseorderdet($this->db);

				if ($licenseOrderDetList->fetchList("fk_commande_det = $object->rowid", '') > 0) {
					foreach ($licenseOrderDetList->dataset as $data) {
						$licenseOrderDet = new Licenseorderdet($this->db);
						$licenseOrderDet->id = $data['rowid'];
						if ($licenseOrderDet->delete($user) > 0) {
							dol_syslog("Trigger '" . $this->name . "' for action '$action' for licenseorder detail" . __FILE__ . ". id=" . $licenseOrderDet->id);
						}
					}
				}
				break;
			}

			// Supplier orders
			//case 'ORDER_SUPPLIER_CREATE':
			//case 'ORDER_SUPPLIER_MODIFY':
			//case 'ORDER_SUPPLIER_VALIDATE':
			//case 'ORDER_SUPPLIER_DELETE':
			//case 'ORDER_SUPPLIER_APPROVE':
			//case 'ORDER_SUPPLIER_CLASSIFY_BILLED':		// TODO Replace with ORDER_SUPPLIER_BILLED
			//case 'ORDER_SUPPLIER_CLASSIFY_UNBILLED':		// TODO Replace with ORDER_SUPPLIER_UNBILLED
			//case 'ORDER_SUPPLIER_REFUSE':
			//case 'ORDER_SUPPLIER_CANCEL':
			//case 'ORDER_SUPPLIER_SENTBYMAIL':
			//case 'ORDER_SUPPLIER_RECEIVE':
			//case 'LINEORDER_SUPPLIER_DISPATCH':
			//case 'LINEORDER_SUPPLIER_CREATE':
			//case 'LINEORDER_SUPPLIER_MODIFY':
			//case 'LINEORDER_SUPPLIER_DELETE':

			// Proposals
			//case 'PROPAL_CREATE':
			//case 'PROPAL_MODIFY':
			//case 'PROPAL_VALIDATE':
			//case 'PROPAL_SENTBYMAIL':
			//case 'PROPAL_CLASSIFY_BILLED':		// TODO Replace it with PROPAL_BILLED
			//case 'PROPAL_CLASSIFY_UNBILLED':		// TODO Replace it with PROPAL_UNBILLED
			//case 'PROPAL_CLOSE_SIGNED':
			//case 'PROPAL_CLOSE_REFUSED':
			//case 'PROPAL_DELETE':
			//case 'LINEPROPAL_INSERT':
			//case 'LINEPROPAL_MODIFY':
			//case 'LINEPROPAL_DELETE':

			// SupplierProposal
			//case 'SUPPLIER_PROPOSAL_CREATE':
			//case 'SUPPLIER_PROPOSAL_MODIFY':
			//case 'SUPPLIER_PROPOSAL_VALIDATE':
			//case 'SUPPLIER_PROPOSAL_SENTBYMAIL':
			//case 'SUPPLIER_PROPOSAL_CLOSE_SIGNED':
			//case 'SUPPLIER_PROPOSAL_CLOSE_REFUSED':
			//case 'SUPPLIER_PROPOSAL_DELETE':
			//case 'LINESUPPLIER_PROPOSAL_INSERT':
			//case 'LINESUPPLIER_PROPOSAL_MODIFY':
			//case 'LINESUPPLIER_PROPOSAL_DELETE':

			// Contracts
			//case 'CONTRACT_CREATE':
			//case 'CONTRACT_MODIFY':
			//case 'CONTRACT_ACTIVATE':
			//case 'CONTRACT_CANCEL':
			//case 'CONTRACT_CLOSE':
			//case 'CONTRACT_DELETE':
			//case 'LINECONTRACT_INSERT':
			//case 'LINECONTRACT_MODIFY':
			//case 'LINECONTRACT_DELETE':

			// Bills
			//case 'BILL_CREATE':
			//case 'BILL_MODIFY':
			//case 'BILL_VALIDATE':
			//case 'BILL_UNVALIDATE':
			//case 'BILL_SENTBYMAIL':
			//case 'BILL_CANCEL':
			//case 'BILL_DELETE':
			//case 'BILL_PAYED':
			//case 'LINEBILL_INSERT':
			//case 'LINEBILL_MODIFY':
			//case 'LINEBILL_DELETE':

			// Recurring Bills
			//case 'BILLREC_MODIFY':
			//case 'BILLREC_DELETE':
			//case 'BILLREC_AUTOCREATEBILL':
			//case 'LINEBILLREC_MODIFY':
			//case 'LINEBILLREC_DELETE':

			//Supplier Bill
			//case 'BILL_SUPPLIER_CREATE':
			//case 'BILL_SUPPLIER_MODIFY':
			//case 'BILL_SUPPLIER_DELETE':
			//case 'BILL_SUPPLIER_PAYED':
			//case 'BILL_SUPPLIER_UNPAYED':
			//case 'BILL_SUPPLIER_VALIDATE':
			//case 'BILL_SUPPLIER_UNVALIDATE':
			//case 'LINEBILL_SUPPLIER_CREATE':
			//case 'LINEBILL_SUPPLIER_MODIFY':
			//case 'LINEBILL_SUPPLIER_DELETE':

			// Payments
			//case 'PAYMENT_CUSTOMER_CREATE':
			//case 'PAYMENT_SUPPLIER_CREATE':
			//case 'PAYMENT_ADD_TO_BANK':
			//case 'PAYMENT_DELETE':

			// Online
			//case 'PAYMENT_PAYBOX_OK':
			//case 'PAYMENT_PAYPAL_OK':
			//case 'PAYMENT_STRIPE_OK':

			// Donation
			//case 'DON_CREATE':
			//case 'DON_MODIFY':
			//case 'DON_DELETE':

			// Interventions
			//case 'FICHINTER_CREATE':
			//case 'FICHINTER_MODIFY':
			//case 'FICHINTER_VALIDATE':
			//case 'FICHINTER_CLASSIFY_BILLED':			// TODO Replace it with FICHINTER_BILLED
			//case 'FICHINTER_CLASSIFY_UNBILLED':		// TODO Replace it with FICHINTER_UNBILLED
			//case 'FICHINTER_DELETE':
			//case 'LINEFICHINTER_CREATE':
			//case 'LINEFICHINTER_MODIFY':
			//case 'LINEFICHINTER_DELETE':

			// Members
			//case 'MEMBER_CREATE':
			//case 'MEMBER_VALIDATE':
			//case 'MEMBER_SUBSCRIPTION':
			//case 'MEMBER_MODIFY':
			//case 'MEMBER_NEW_PASSWORD':
			//case 'MEMBER_RESILIATE':
			//case 'MEMBER_DELETE':

			// Categories
			//case 'CATEGORY_CREATE':
			//case 'CATEGORY_MODIFY':
			//case 'CATEGORY_DELETE':
			//case 'CATEGORY_SET_MULTILANGS':

			// Projects
			//case 'PROJECT_CREATE':
			//case 'PROJECT_MODIFY':
			//case 'PROJECT_DELETE':

			// Project tasks
			//case 'TASK_CREATE':
			//case 'TASK_MODIFY':
			//case 'TASK_DELETE':

			// Task time spent
			//case 'TASK_TIMESPENT_CREATE':
			//case 'TASK_TIMESPENT_MODIFY':
			//case 'TASK_TIMESPENT_DELETE':
			//case 'PROJECT_ADD_CONTACT':
			//case 'PROJECT_DELETE_CONTACT':
			//case 'PROJECT_DELETE_RESOURCE':

			// Shipping
			//case 'SHIPPING_CREATE':
			//case 'SHIPPING_MODIFY':
			//case 'SHIPPING_VALIDATE':
			//case 'SHIPPING_SENTBYMAIL':
			//case 'SHIPPING_BILLED':
			//case 'SHIPPING_CLOSED':
			//case 'SHIPPING_REOPEN':
			//case 'SHIPPING_DELETE':

			// and more...

			default:
				dol_syslog("Trigger '".$this->name."' for action '".$action."' launched by ".__FILE__.". id=".$object->id);
				break;
		}

		return 0;
	}
}
