<?php
/* Copyright (C) 2007-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
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
 *  \file       htdocs/licensemanager/class/licenseorder.class.php
 *  \ingroup    licensemanager commande
 *  \brief      This file is a CRUD class file (Create/Read/Update/Delete) for the license_order table
 *				Initialy built by build_class_from_table on 2013-12-28 16:27
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
dol_include_once('/licensemanager/lib/phpqrcode.php');

/**
 *	Put here description of your class
 */
class Licenseorder extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='licenseorder';			//!< Id that identify managed objects
	var $table_element='licenseorder';		//!< Name of table without prefix where object is stored

    var $id;
    
	var $tms='';
	var $fk_customer;
	var $fk_commande;
	var $note;
	var $output_mode;
	var $key_mode;
	var $qty_seq_id;
	var $fk_user_author;
	var $identification; 
	
	// array with multiple records
	public $dataset=array();

    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    function __construct($db)
    {
        $this->db = $db;
        return 1;
    }


    /**
     *  Create object into database
     *
     *  @param	User	$user        User that creates
     *  @param  int		$notrigger   0=launch triggers after, 1=disable triggers
     *  @return int      		   	 <0 if KO, Id of created object if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->note)) $this->note=trim($this->note);
		if (isset($this->identification)) $this->identification = trim($this->identification);
		if (isset($this->output_mode)) $this->output_mode=trim($this->output_mode);
		if (isset($this->key_mode)) $this->key_mode=trim($this->key_mode);
		
		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."license_order(";
		
		$sql.= "fk_customer,";
		$sql.= "fk_commande,";
		$sql.= "note,";
		$sql.= "output_mode,";
		$sql.= "key_mode,";
		$sql.= "qty_seq_id,";
		$sql.= "fk_user_author,";
		$sql.= "identification";
		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->fk_customer)?'NULL':$this->fk_customer).",";
		$sql.= " ".(! isset($this->fk_commande)?'NULL':$this->fk_commande).",";
		$sql.= " ".(! isset($this->note)?'NULL':"'".$this->db->escape($this->note)."'").",";
		$sql.= " ".(! isset($this->output_mode)?'NULL':"'".$this->db->escape($this->output_mode)."'").",";
		$sql.= " ".(! isset($this->key_mode)?'NULL':"'".$this->db->escape($this->key_mode)."'").",";
		$sql.= " ".(! isset($this->qty_seq_id)?'NULL':$this->qty_seq_id).",";
		$sql.= " ".(! isset($this->fk_user_author)?'NULL':$user->id).",";
		$sql.= " ".(! isset($this->identification)?'NULL':"'".$this->db->escape($this->identification)."'")."";
		 
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."license_order");

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::create ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }


    /**
     *  Load object in memory from the database
     *
     *  @param	int		$id    Id object
     *  @param	int		$fk_commande    Id related order
     *  @param  int		$qty_seq_id		qty sequence number
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id,$fk_commande,$qty_seq_id)
    {
    	global $langs;
        
    	if (($id > 0) || ($fk_commande > 0))
    	{
    	
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
			$sql.= " t.identification";
			
	        $sql.= " FROM ".MAIN_DB_PREFIX."license_order as t";
    		if ($id > 0)
	        {
	        	$sql.= " WHERE t.rowid = ".$id;
	        }
	        if (($id == 0)&&($fk_commande > 0))
	        {
	        	$sql.= " WHERE t.fk_commande = ".$fk_commande;
	        }
	        if (($fk_commande == 0) && ($qty_seq_id > 0))
	        {
	        	$sql.= " WHERE t.qty_seq_id = ".$qty_seq_id;
	        } else if (($fk_commande > 0) && ($qty_seq_id > 0))
	        {
	        	$sql.= " AND t.qty_seq_id = ".$qty_seq_id;
	        }
	
	
	    	dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
	        $resql=$this->db->query($sql);
	        if ($resql)
	        {
	            $row=0;
	        	if ($this->db->num_rows($resql))
	            {
	                $obj = $this->db->fetch_object($resql);
	
	                $this->id    = $obj->rowid;
	                
					$this->tms = $this->db->jdate($obj->tms);
					$this->fk_customer = $obj->fk_customer;
					$this->fk_commande = $obj->fk_commande;
					$this->note = $obj->note;
					$this->output_mode = $obj->output_mode;
					$this->key_mode = $obj->key_mode;
					$this->qty_seq_id = $obj->qty_seq_id;
					$this->fk_user_author = $obj->fk_user_author;
					$this->identification = $obj->identification;
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
    	}  else
    	{
    		$this->error="Parameter Error ";
	        dol_syslog(get_class($this)."::fetch ".$this->error, LOG_ERR);
	        return -2;
    	}
    }

    /**
     *  Load object in memory from the database
     *
     *  @param	string		$filter     where clause string
     *  @param	string		$orderBy    order by string
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetchList($filter = '',$orderBy = '')
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
    	$sql.= " t.identification";
        			
    	$sql.= " FROM ".MAIN_DB_PREFIX."license_order as t";
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
    			$this->dataset[$row]['fk_customer'] = $obj->fk_customer;
    			$this->dataset[$row]['fk_commande'] = $obj->fk_commande;
    			$this->dataset[$row]['note'] = $obj->note;
    			$this->dataset[$row]['output_mode'] = $obj->output_mode;
    			$this->dataset[$row]['key_mode'] = $obj->key_mode;
    			$this->dataset[$row]['qty_seq_id'] = $obj->qty_seq_id;
    			$this->dataset[$row]['fk_user_author'] = $obj->fk_user_author;
    			$this->dataset[$row]['identification'] = $obj->identification;
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
     *  Update object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user=0, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->note)) $this->note=trim($this->note);
		if (isset($this->identification)) $this->identification=trim($this->identification);
		if (isset($this->output_mode)) $this->output_mode=trim($this->output_mode);
		if (isset($this->key_mode)) $this->key_mode=trim($this->key_mode);
		
		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."license_order SET";
        
		$sql.= " tms=".(dol_strlen($this->tms)!=0 ? "'".$this->db->idate($this->tms)."'" : 'null').",";
		$sql.= " fk_customer=".(isset($this->fk_customer)?$this->fk_customer:"null").",";
		$sql.= " fk_commande=".(isset($this->fk_commande)?$this->fk_commande:"null").",";
		$sql.= " note=".(isset($this->note)?"'".$this->db->escape($this->note)."'":"null").",";
		$sql.= " output_mode=".(isset($this->output_mode)?"'".$this->db->escape($this->output_mode)."'":"null").",";
		$sql.= " key_mode=".(isset($this->key_mode)?"'".$this->db->escape($this->key_mode)."'":"null").",";
		$sql.= " qty_seq_id=".(isset($this->qty_seq_id)?$this->qty_seq_id:"null").",";
		$sql.= " fk_user_author=".(isset($user->id)?$user->id:"null").",";
		$sql.= " identification=".(isset($this->identification)?"'".$this->db->escape($this->identification)."'":"null")."";
		
        
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(get_class($this)."::update sql=".$sql, LOG_DEBUG);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_MODIFY',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
	    	}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::update ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
    }


 	/**
	 *  Delete object in database
	 *
     *	@param  User	$user        User that deletes
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function delete($user, $notrigger=0)
	{
		global $conf, $langs;
		$error=0;

		$this->db->begin();

		if (! $error)
		{
			if (! $notrigger)
			{
				// Uncomment this and change MYOBJECT to your own tag if you
		        // want this action calls a trigger.

		        //// Call triggers
		        //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
		        //$interface=new Interfaces($this->db);
		        //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
		        //if ($result < 0) { $error++; $this->errors=$interface->errors; }
		        //// End call triggers
			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."license_order";
    		$sql.= " WHERE rowid=".$this->id;

    		dol_syslog(get_class($this)."::delete sql=".$sql);
    		$resql = $this->db->query($sql);
        	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(get_class($this)."::delete ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
	}



	/**
	 *	Load an object from its id and create a new one in database
	 *
	 *	@param	int		$fromid     Id of object to clone
	 * 	@return	int					New id of clone
	 */
	function createFromClone($fromid)
	{
		global $user,$langs;

		$error=0;

		$object=new Licenseorder($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0)
		{
			$this->error=$object->error;
			$error++;
		}

		if (! $error)
		{


		}

		// End
		if (! $error)
		{
			$this->db->commit();
			return $object->id;
		}
		else
		{
			$this->db->rollback();
			return -1;
		}
	}

	
	
	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;
		
		$this->tms='';
		$this->fk_customer=0;
		$this->fk_commande=0;
		$this->note='';
		$this->output_mode='';
		$this->key_mode='';
		$this->qty_seq_id=0;
		$this->fk_user_author=0;
		$this->output_mode='';
		$this->identification='';
	}
}

/**
 *	License properties
 */
Class License
{
	public $code;
	public $key_mode;
	public $ouput_mode;
}
?>
