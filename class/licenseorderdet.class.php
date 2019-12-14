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
 *  \file       htdocs/licensemanager/class/licenseorderdet.class.php
 *  \ingroup    licensemanager commande
 *  \brief      This file is a CRUD class file (Create/Read/Update/Delete) for the license_orderdet table
 *				Initialy built by build_class_from_table on 2013-12-28 16:27
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Licenseorderdet extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='licenseorderdet';			//!< Id that identify managed objects
	var $table_element='licenseorderdet';		//!< Name of table without prefix where object is stored

    var $id;
    
	var $tms='';
	var $fk_license_product;
	var $fk_license_list;
	var $fk_license_order;
	var $fk_commande_det;
	var $datec='';
	var $datev='';
	var $license_key='';
	
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

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."license_orderdet(";
		
		$sql.= "fk_license_product,";
		$sql.= "fk_license_list,";
		$sql.= "fk_license_order,";
		$sql.= "fk_commande_det,";
		$sql.= "datec,";
		$sql.= "datev,";
		$sql.= "license_key";
		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->fk_license_product)?'NULL':$this->fk_license_product).",";
		$sql.= " ".(! isset($this->fk_license_list)?'NULL':$this->fk_license_list).",";
		$sql.= " ".(! isset($this->fk_license_order)?'NULL':$this->fk_license_order).",";
		$sql.= " ".(! isset($this->fk_commande_det)?'NULL':$this->fk_commande_det).",";
		$sql.= " ".(! isset($this->datec) || dol_strlen($this->datec)==0 ? 'NULL' : "'".$this->db->idate($this->datec))."'".",";
		$sql.= " ".(! isset($this->datev) || dol_strlen($this->datev)==0 ? 'NULL' : "'".$this->db->idate($this->datev))."'".",";
        $sql.= " ".(! isset($this->license_key) || dol_strlen($this->license_key)==0?'NULL':"'".$this->db->escape($this->license_key)."'")."";
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."license_orderdet");

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
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id)
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
        $sql.= " WHERE t.rowid = ".$id;

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
				$this->fk_license_product = $obj->fk_license_product;
				$this->fk_license_list = $obj->fk_license_list;
				$this->fk_license_order = $obj->fk_license_order;
				$this->fk_commande_det = $obj->fk_commande_det;
				$this->datec = $this->db->jdate($obj->datec);
				$this->datev = $this->db->jdate($obj->datev); 
				$this->license_key = $obj->license_key;               
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
     *  Load all objects in memory from database
     *
     *  @param	string		$filter     where clause string
     *  @param	string		$orderBy    order by string
     *  @return int          	<0 if KO, >0 if OK (nbr rows)
     */
    function fetchList($filter = '',$orderBy = '')
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

		 // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."license_orderdet SET";
        
		$sql.= " tms=".(dol_strlen($this->tms)!=0 ? "'".$this->db->idate($this->tms)."'" : 'null').",";
		$sql.= " fk_license_product=".(isset($this->fk_license_product)?$this->fk_license_product:"null").",";
		$sql.= " fk_license_list=".(isset($this->fk_license_list)?$this->fk_license_list:"null").",";
		$sql.= " fk_license_order=".(isset($this->fk_license_order)?$this->fk_license_order:"null").",";
		$sql.= " fk_commande_det=".(isset($this->fk_commade_det)?$this->fk_commande_det:"null").",";
		$sql.= " datec=".(dol_strlen($this->datec)!=0 ? "'".$this->db->idate($this->datec)."'" : 'null').",";
		$sql.= " datev=".(dol_strlen($this->datev)!=0 ? "'".$this->db->idate($this->datev)."'" : 'null').",";
		$sql.= " license_key=".(dol_strlen($this->license_key)!=0 ? "'".$this->db->escape($this->license_key)."'" : 'null')."";
		
        
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
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."license_orderdet";
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

		$object=new Licenseorderdet($this->db);

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
		$this->fk_license_product=0;
		$this->fk_license_list=0;
		$this->fk_license_order=0;
		$this->fk_commande_det=0;
		$this->datec='';
		$this->datev='';
		$this->license_key='';
	}

}
?>
