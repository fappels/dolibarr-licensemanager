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
 *  \file       htdocs/licensemanager/class/licensekeylist.class.php
 *  \ingroup    licensemanager
 *  \brief      This file is a CRUD class file (Create/Read/Update/Delete) for the license_keylist table
 *				Initialy built by build_class_from_table on 2013-12-28 16:25
 */

// Put here all includes required by your class file
require_once(DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php");
//require_once(DOL_DOCUMENT_ROOT."/societe/class/societe.class.php");
//require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");


/**
 *	Put here description of your class
 */
class Licensekeylist extends CommonObject
{
	var $db;							//!< To store db handler
	var $error;							//!< To return error code (or message)
	var $errors=array();				//!< To return several error codes (or messages)
	var $element='licensekeylist';			//!< Id that identify managed objects
	var $table_element='licensekeylist';		//!< Name of table without prefix where object is stored

    var $id;
    
	var $tms='';
	var $type;
	var $algo;
	var $option_code;
	var $base_key;
	var $name;
	var $mode;
	var $multi_key_separator;
	var $output_mode;
	var $duration;
	var $duration_unit;
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
        
		if (isset($this->type)) $this->type=trim($this->type);
		if (isset($this->algo)) $this->algo=trim($this->algo);
		if (isset($this->option_code)) $this->option_code=trim($this->option_code);
		if (isset($this->base_key)) $this->base_key=trim($this->base_key);
		if (isset($this->name)) $this->name=trim($this->name);     
		if (isset($this->multi_key_separator)) $this->multi_key_separator=trim($this->multi_key_separator);
		if (isset($this->output_mode)) $this->output_mode=trim($this->output_mode);
		
		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX."license_keylist(";
		
		$sql.= "type,";
		$sql.= "algo,";
		$sql.= "option_code,";
		$sql.= "base_key,";
		$sql.= "name,";
		$sql.= "mode,";
		$sql.= "multi_key_separator,";
		$sql.= "output_mode,";
		$sql.= "duration,";
		$sql.= "duration_unit";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->type)?'NULL':$this->type).",";
		$sql.= " ".(! isset($this->algo)?'NULL':"'".$this->db->escape($this->algo)."'").",";
		$sql.= " ".(! isset($this->option_code)?'NULL':"'".$this->db->escape($this->option_code)."'").",";
		$sql.= " ".(! isset($this->base_key)?'NULL':"'".$this->db->escape($this->base_key)."'").",";
		$sql.= " ".(! isset($this->name)?'NULL':"'".$this->db->escape($this->name)."'").",";
		$sql.= " ".(! isset($this->mode)?'NULL':"'".$this->db->escape($this->mode)."'").",";
		$sql.= " ".(! isset($this->multi_key_separator)?'NULL':"'".$this->db->escape($this->multi_key_separator)."'").",";
		$sql.= " ".(! isset($this->output_mode)?'NULL':"'".$this->db->escape($this->output_mode)."'").",";
		$sql.= " ".(! isset($this->duration)?'NULL':"'".$this->db->escape($this->duration)."'").",";
		$sql.= " ".(! isset($this->duration_unit)?'NULL':"'".$this->db->escape($this->duration_unit)."'")."";
  
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(get_class($this)."::create sql=".$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX."license_keylist");

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
		$sql.= " t.type,";
		$sql.= " t.algo,";
		$sql.= " t.option_code,";
		$sql.= " t.base_key,";
		$sql.= " t.name,";
		$sql.= " t.mode,";
		$sql.= " t.multi_key_separator,";
		$sql.= " t.output_mode,";
		$sql.= " t.duration,";
		$sql.= " t.duration_unit";
		
        $sql.= " FROM ".MAIN_DB_PREFIX."license_keylist as t";
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
				$this->type = $obj->type;
				$this->algo = $obj->algo;
				$this->option_code = $obj->option_code;
				$this->base_key = $obj->base_key;
				$this->name = $obj->name;
				$this->mode = $obj->mode;
				$this->multi_key_separator = $obj->multi_key_separator;
				$this->output_mode = $obj->output_mode;
				$this->duration = $obj->duration;
				$this->duration_unit = $obj->duration_unit;
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
    public function fetchList($filter = '',$orderBy = '')
    {
    	global $langs;
    	 
    	$sql = "SELECT";
    	$sql.= " t.rowid,";
		$sql.= " t.tms,";
		$sql.= " t.type,";
		$sql.= " t.algo,";
		$sql.= " t.option_code,";
		$sql.= " t.base_key,";
		$sql.= " t.name,";
		$sql.= " t.mode,";
		$sql.= " t.multi_key_separator,";
    	$sql.= " t.output_mode,";
    	$sql.= " t.duration,";
		$sql.= " t.duration_unit";
		$sql.= " FROM ".MAIN_DB_PREFIX."license_keylist as t";
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
    			$this->dataset[$row]['rowid'] 	= $obj->rowid;
    			$this->dataset[$row]['tms'] 	= $obj->tms;
    			$this->dataset[$row]['type'] 	= $obj->type;
    			$this->dataset[$row]['algo']	= $obj->algo;
    			$this->dataset[$row]['option_code'] 	= $obj->option_code;
    			$this->dataset[$row]['base_key'] 	= $obj->base_key;
    			$this->dataset[$row]['name'] 	= $obj->name;
    			$this->dataset[$row]['mode']	= $obj->mode;
    			$this->dataset[$row]['multi_key_separator'] = $obj->multi_key_separator;
    			$this->dataset[$row]['output_mode'] = $obj->output_mode;
    			$this->dataset[$row]['duration'] 	= $obj->duration;
    			$this->dataset[$row]['duration_unit'] = $obj->duration_unit;
    			$row++;
    		}
    		$this->db->free($resql);
    
    		return $row;
    	}
    	else
    	{
    		$this->error="Error ".$this->db->lasterror();
    		dol_syslog(get_class($this)."::read ".$this->error, LOG_ERR);
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
        
		if (isset($this->algo)) $this->algo=trim($this->algo);
		if (isset($this->option_code)) $this->option_code=trim($this->option_code);
		if (isset($this->base_key)) $this->base_key=trim($this->base_key);
		if (isset($this->name)) $this->name=trim($this->name);        
		if (isset($this->multi_key_separator)) $this->multi_key_separator=trim($this->multi_key_separator);
		if (isset($this->output_mode)) $this->output_mode=trim($this->output_mode);
		
		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX."license_keylist SET";
        
		$sql.= " tms=".(dol_strlen($this->tms)!=0 ? "'".$this->db->idate($this->tms)."'" : 'null').",";
		$sql.= " type=".(isset($this->type)?$this->type:"null").",";
		$sql.= " algo=".(isset($this->algo)?"'".$this->db->escape($this->algo)."'":"null").",";
		$sql.= " option_code=".(isset($this->option_code)?"'".$this->db->escape($this->option_code)."'":"null").",";
		$sql.= " base_key=".(isset($this->base_key)?"'".$this->db->escape($this->base_key)."'":"null").",";
		$sql.= " name=".(isset($this->name)?"'".$this->db->escape($this->name)."'":"null").",";
		$sql.= " mode=".(isset($this->mode)?"'".$this->db->escape($this->mode)."'":"null").",";
		$sql.= " multi_key_separator=".(isset($this->multi_key_separator)?"'".$this->db->escape($this->multi_key_separator)."'":"null").",";
		$sql.= " output_mode=".(isset($this->output_mode)?"'".$this->db->escape($this->output_mode)."'":"null").",";
		$sql.= " duration=".(isset($this->duration)?"'".$this->db->escape($this->duration)."'":"null").",";
		$sql.= " duration_unit=".(isset($this->duration_unit)?"'".$this->db->escape($this->duration_unit)."'":"null")."";
		
        
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
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX."license_keylist";
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
	 *  Delete not used licenses from database
	 *
	 *	@param  User	$user        User that deletes
	 *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	function clean($user, $notrigger=0)
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
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."license_keylist";
			$sql.= " WHERE rowid NOT IN (";
			$sql.= " SELECT fk_base_key FROM ".MAIN_DB_PREFIX."license_product";
			$sql.= " UNION SELECT fk_base_key FROM ".MAIN_DB_PREFIX."license_list)";
	
			dol_syslog(get_class($this)."::clean sql=".$sql);
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

		$object=new Licensekeylist($this->db);

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
		$this->type=0;
		$this->algo='';
		$this->option_code='';
		$this->base_key='';
		$this->name='';
		$this->mode='';
		$this->multi_key_separator='';
		$this->output_mode='';
		$this->duration=0;
		$this->duration_unit=0;

		
	}
	
	/** 
	 * generate public key from private key
	 * 
	 * @param array $hashData data to be incremental hmac hashed with private key
	 * 
	 * @return string hashed key
	 */
	function generate($hashData) {
		if (isset($hashData))
		{
			$ctx = hash_init($this->algo,HASH_HMAC,$this->base_key);
			foreach ($hashData as $data)
			{
				hash_update($ctx, $data);
			}
			return hash_final($ctx);
		}
	}
	
	/**
	 *	get available modes for licensekeylist class
	 *
	 *	@return	array modes
	 */
	public static function getModes()
	{
		return array('single','multi');
	}
	
	/**
	 *	get available modes for licensekeylist class
	 *
	 *	@param int $plural return plural values
	 *	@return	array modes
	 */
	public static function getDurationUnits($plural=0)
	{
		global $langs;
		if ($plural)
		{
			return array('d' => $langs->trans('DurationDays'),'w' => $langs->trans('DurationWeeks'),'m' => $langs->trans('DurationMonths'),'y' => $langs->trans('DurationYears'));
		} else 
		{
			return array('d' => $langs->trans('DurationDay'),'w' => $langs->trans('DurationWeek'),'m' => $langs->trans('DurationMonth'),'y' => $langs->trans('DurationYear'));
		}
		
	}
	
	/**
	 *	get available modes for licensekeylist class
	 *
	 *	@return	array types
	 */
	public static function getKeyTypes()
	{
		global $langs;
		
		$langs->load("licensemanager@licensemanager");
		return array(0 => $langs->trans('PrivateKey'),1 => $langs->trans('PickKeyFromList'));
		
	}
	
	/**
	 *	get available algos for licensekeylist class
	 *
	 *	@return	array algo's
	 */
	function getAlgos()
	{
		return hash_algos();
	}
	
	/**
	 *	get available output_modes for licenseorder class
	 *
	 *	@return	array output_modes
	 */
	public static function getOutputModes()
	{
		global $langs;
		$langs->load("licensemanager@licensemanager");
	
		return array(
				'text'=>$langs->trans('Text'),
				'QRCODE,L' => $langs->trans('QRcodeErrorCorrectionLow'),
				'QRCODE,M' => $langs->trans('QRcodeErrorCorrectionMedium'),
				'QRCODE,Q' => $langs->trans('QRcodeErrorCorrectionQuartile'),
				'QRCODE,H' => $langs->trans('QRcodeErrorCorrectionHigh')
		);
	}
	
	/**
	 * check if barcode is 2D
	 *
	 * @param string $code TCPDF barcode type string
	 * @return bool true = yes
	 */
	
	public static function is2d($code) {
		return in_array($code, array('QRCODE,L','QRCODE,M','QRCODE,Q','QRCODE,H'));
	}
}
?>
