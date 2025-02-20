<?php
/* Copyright (C) 2005-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2011 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2014	   Francis Appels       <francis.appels@z-application.com>
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
 *  \file       htdocs/licensemanager/core/triggers/interface_99_modLicenseManager_LicenseManagerSynchro.class.php
 *  \ingroup    licensemanager
 *  \brief      licensemanager triggers
 *  \remarks    - trigger when order line is added
 */

/**
 *  Class of triggers for demo module
 */
class InterfaceLicenseManagerSynchro
{
    var $db;

    /**
     *   Constructor
     *
     *   @param		DoliDB		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "module";
        $this->description = "Triggers of this module synchronise user modifaction with the licensemanager module.";
        $this->version = '1.0.1';            // 'development', 'experimental', 'dolibarr' or version
        $this->picto = 'technic';
    }


    /**
     *   Return name of trigger file
     *
     *   @return     string      Name of trigger file
     */
    function getName()
    {
        return $this->name;
    }

    /**
     *   Return description of trigger file
     *
     *   @return     string      Description of trigger file
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   Return version of trigger file
     *
     *   @return     string      Version of trigger file
     */
    function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') return $langs->trans("Development");
        elseif ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }

    /**
     *      Function called when a Dolibarrr business event is done.
     *      All functions "run_trigger" are triggered if file is inside directory htdocs/core/triggers
     *
     *      @param	string		$action		Event action code
     *      @param  Object		$object     Object
     *      @param  User		$user       Object user
     *      @param  Translate	$langs      Object langs
     *      @param  conf		$conf       Object conf
     *      @return int         			<0 if KO, 0 if no triggered ran, >0 if OK
     */
	function run_trigger($action,$object,$user,$langs,$conf)
    {
        // Put here code you want to execute when a Dolibarr business events occurs.
        // Data and type of action are stored into $object and $action
    	$now=dol_now();
        // Users
        /*if ($action == 'USER_LOGIN')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_UPDATE_SESSION')
        {
            // Warning: To increase performances, this action is triggered only if
            // constant MAIN_ACTIVATE_UPDATESESSIONTRIGGER is set to 1.
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_CREATE_FROM_CONTACT')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_NEW_PASSWORD')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_ENABLEDISABLE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_LOGOUT')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_SETINGROUP')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'USER_REMOVEFROMGROUP')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

		// Groups
		elseif ($action == 'GROUP_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'GROUP_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'GROUP_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Companies
        elseif ($action == 'COMPANY_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'COMPANY_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'COMPANY_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Contacts
        elseif ($action == 'CONTACT_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'CONTACT_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'CONTACT_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Products
        elseif ($action == 'PRODUCT_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PRODUCT_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        else*/if ($action == 'PRODUCT_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
            dol_include_once('/licensemanager/class/licenseproduct.class.php');

            $licenseProduct = new Licenseproduct($this->db);
            if ($licenseProduct->fetch(0,$object->id) > 0) {
            	if ($licenseProduct->delete($user)<0) {
            		dol_syslog("Trigger failed'".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
            	}
            }
        }/*

		// Customer orders
        elseif ($action == 'ORDER_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'ORDER_CLONE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'ORDER_VALIDATE')
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }*/
        elseif ($action == 'ORDER_DELETE')
        {
        // delete licenseorder and licenseorderdet
        	require_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");
            dol_include_once('/licensemanager/class/licenseorder.class.php');

            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
            $licenseOrderList = new Licenseorder($this->db);
            if ($licenseOrderList->fetchList("fk_commande = $object->id",'') > 0)
            {
            	foreach ($licenseOrderList->dataset as $data)
            	{
            		$licenseOrder = new Licenseorder($this->db);
            		if ($licenseOrder->fetch($data['rowid'], 0,0) > 0)
            		{
            			$licenseOrderDetList = new Licenseorderdet($this->db);

            			if ($licenseOrderDetList->fetchList("fk_license_order = $licenseOrder->id",'') > 0)
            			{
            				foreach($licenseOrderDetList->dataset as $data)
            				{
            					$licenseOrderDet = new Licenseorderdet($this->db);
            					$licenseOrderDet->id = $data['rowid'];
            					if ($licenseOrderDet->delete($user) > 0)
            					{
            						dol_syslog("Trigger '".$this->name."' for action '$action' for licenseorder detail ".__FILE__.". id=".$licenseOrderDet->id);
            					}
            				}
            			}
            			if ($licenseOrder->delete($user, 0))
            			{
            				dol_syslog("Trigger '".$this->name."' for action '$action' for licenseorder ".__FILE__.". id=".$licenseOrder->id);
            			}
            		}
            	}

            }
        }/*
        elseif ($action == 'ORDER_BUILDDOC')
        {
            global $langs;
        	dol_include_once('/licensemanager/class/pdf_license.class.php');

            $pdfLicense = new pdf_license($this->db);
            if ($pdfLicense->write_file($object, $langs) > 0)
            {
            	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
            } else
            {
            	dol_syslog("Trigger '".$this->name."' for action '$action' has error ".__FILE__.":".$pdfLicense->error);

            }
        }
        elseif ($action == 'ORDER_SENTBYMAIL')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'ORDER_CLASSIFY_BILLED')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }*/
        elseif ($action == 'LINEORDER_INSERT')
        {
        	require_once(DOL_DOCUMENT_ROOT."/commande/class/commande.class.php");
        	require_once(DOL_DOCUMENT_ROOT."/core/lib/date.lib.php");
        	dol_include_once('/licensemanager/class/licensekeylist.class.php');
        	dol_include_once('/licensemanager/class/licenseproduct.class.php');
        	dol_include_once('/licensemanager/class/licenseorder.class.php');
        	//launch
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->rowid);
        	$orderLine = new OrderLine($this->db);
            $order = new Commande($this->db);
            $orderLine->fetch($object->rowid);

            if ($order->fetch($orderLine->fk_commande) > 0)
            {
            	$licenseProduct = new Licenseproduct($this->db);
            	if ($licenseProduct->fetch(0,$orderLine->fk_product) > 0)
	           	{
	           		$licenseKeylist = new Licensekeylist($this->db);
	           		if ($licenseKeylist->fetch($licenseProduct->fk_base_key) > 0)
	           		{
	           			for ($i=1;$i<=$orderLine->qty;$i++)
	           			{
	           				$licenseOrder = new Licenseorder($this->db);
	           				$result = $licenseOrder->fetch(0, $order->id,$i);
	           				if (($result>0) && ($licenseOrder->id > 0) && ($licenseOrder->key_mode == $licenseKeylist->mode))
	           				{
	           					// only add licenses of same mode to license order (single or multi)
	           					if ($this->createLicenseOrderDet($user, $licenseKeylist,$licenseOrder->id,$licenseProduct->id,$object->rowid) > 0)
	           					{
	           						dol_syslog("Trigger '".$this->name."' for action '$action' for product id=".$licenseProduct->fk_product);
	           					}
	           				}
	           				else
	           				{
	           					$licenseOrder->fk_customer = $order->socid;
	           					$licenseOrder->fk_commande = $order->id;
	           					$licenseOrder->fk_user_author = $user->id;
	           					// and copy mode and output_mode to order
	           					$licenseOrder->output_mode = $licenseKeylist->output_mode;
	           					$licenseOrder->key_mode = $licenseKeylist->mode;
	           					$licenseOrder->qty_seq_id = $i;
	           					if ($licenseOrder->create($user) > 0)
	           					{
	           						if ($this->createLicenseOrderDet($user, $licenseKeylist,$licenseOrder->id,$licenseProduct->id,$object->rowid) > 0)
	           						{
	           							dol_syslog("Trigger '".$this->name."' for action '$action' for product id=".$licenseProduct->fk_product);
	           						}
	           					}
	           				}
	           			}
	           		}
            	}
            }
        }
        elseif ($action == 'LINEORDER_DELETE')
        {
            // delete licenseorderdet
            dol_include_once('/licensemanager/class/licenseorder.class.php');

            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->rowid);
        	$licenseOrderDetList = new Licenseorderdet($this->db);

        	if ($licenseOrderDetList->fetchList("fk_commande_det = $object->rowid",'') > 0)
        	{
        		foreach($licenseOrderDetList->dataset as $data)
        		{
        			$licenseOrderDet = new Licenseorderdet($this->db);
        			$licenseOrderDet->id = $data['rowid'];
        			if ($licenseOrderDet->delete($user) > 0)
        			{
        				dol_syslog("Trigger '".$this->name."' for action '$action' for licenseorder detail".__FILE__.". id=".$licenseOrderDet->id);
        			}
        		}
        	}
        }/*

		// Supplier orders
        elseif ($action == 'ORDER_SUPPLIER_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'ORDER_SUPPLIER_VALIDATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'ORDER_SUPPLIER_SENTBYMAIL')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'ORDER_SUPPLIER_BUILDDOC')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Proposals
        elseif ($action == 'PROPAL_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PROPAL_CLONE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PROPAL_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PROPAL_VALIDATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PROPAL_BUILDDOC')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PROPAL_SENTBYMAIL')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PROPAL_CLOSE_SIGNED')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PROPAL_CLOSE_REFUSED')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PROPAL_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'LINEPROPAL_INSERT')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'LINEPROPAL_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'LINEPROPAL_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Contracts
        elseif ($action == 'CONTRACT_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'CONTRACT_ACTIVATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'CONTRACT_CANCEL')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'CONTRACT_CLOSE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'CONTRACT_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Bills
        elseif ($action == 'BILL_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'BILL_CLONE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'BILL_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'BILL_VALIDATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
		elseif ($action == 'BILL_UNVALIDATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'BILL_BUILDDOC')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'BILL_SENTBYMAIL')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'BILL_CANCEL')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'BILL_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
		elseif ($action == 'LINEBILL_INSERT')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
		elseif ($action == 'LINEBILL_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Payments
        elseif ($action == 'PAYMENT_CUSTOMER_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PAYMENT_SUPPLIER_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PAYMENT_ADD_TO_BANK')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PAYMENT_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

		// Interventions
		elseif ($action == 'FICHINTER_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'FICHINTER_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'FICHINTER_VALIDATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
	    elseif ($action == 'FICHINTER_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Members
        elseif ($action == 'MEMBER_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'MEMBER_VALIDATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'MEMBER_SUBSCRIPTION')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'MEMBER_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'MEMBER_NEW_PASSWORD')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'MEMBER_RESILIATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'MEMBER_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Categories
        elseif ($action == 'CATEGORY_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'CATEGORY_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'CATEGORY_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Projects
        elseif ($action == 'PROJECT_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PROJECT_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'PROJECT_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Project tasks
        elseif ($action == 'TASK_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'TASK_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'TASK_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Task time spent
        elseif ($action == 'TASK_TIMESPENT_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'TASK_TIMESPENT_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'TASK_TIMESPENT_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }

        // Shipping
        elseif ($action == 'SHIPPING_CREATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'SHIPPING_MODIFY')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'SHIPPING_VALIDATE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'SHIPPING_SENTBYMAIL')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'SHIPPING_DELETE')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
        elseif ($action == 'SHIPPING_BUILDDOC')
        {
            dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);
        }
		*/
		return 0;
    }

    /**
     * function to create licenses for products with licenses assigned
     *
     * @param object $licenseKeylist $license source object
     * @param int $fk_license_order parent license order id
     * @param int $fk_license_product related product id
     * @param int $fk_commande_det related order line
     *
     * @return int create result NOK < 0 OK > 0
     */

    private function createLicenseOrderDet($user, $licenseKeylist, $fk_license_order, $fk_license_product, $fk_commande_det)
    {
    	$now=dol_now();
	    $licenseOrderDet = new Licenseorderdet($this->db);
	    $licenseOrderDet->fk_license_order = $fk_license_order;
	    $licenseOrderDet->fk_license_product = $fk_license_product;
	    $licenseOrderDet->fk_commande_det = $fk_commande_det;
	    $licenseOrderDet->datec = $now;
	    $licenseOrderDet->datev = dol_time_plus_duree($now,$licenseKeylist->duration,$licenseKeylist->duration_unit);
	    return $licenseOrderDet->create($user);
    }
}
?>
