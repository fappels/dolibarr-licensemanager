<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2008 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2011 Regis Houssin        <regis@dolibarr.fr>
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
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/licensemanager/class/pdf_license.class.php
 *	\ingroup    licensemanager
 *	\brief      File of class to manage license document for licensemanager module
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commondocgenerator.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

dol_include_once('/licensemanager/class/licenseorder.class.php');
dol_include_once('/licensemanager/class/licenseproduct.class.php');
dol_include_once('/licensemanager/class/licensekeylist.class.php');
dol_include_once('/licensemanager/class/licenselist.class.php');

/**
 *	\class      pdf_license
 *	\brief      Class te generate license document for an order containing licenses
 */
class pdf_license extends CommonDocGenerator
{
	// private properties
	private $_db;
	private $_sender;
	private $_format;
	private $_bottom_margin;
	private $_right_margin;
	private $_left_margin;
	private $_top_margin;
	private $_page_height;
	private $_page_width;

	// public properties
	public $name;
	public $description;
	public $type;

	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
		global $conf,$langs,$mysoc;

        $langs->load("main");
        $langs->load("orders");
		$langs->load("licensemanager@licensemanager");
		$langs->load("companies");

        $this->_db = $db;
		$this->name = "license";
		$this->description = $langs->trans("DocumentLicense");

		// Dimension page
		$this->type = 'pdf';
		$formatarray=pdf_getFormat();
		$this->_page_width = $formatarray['width'];
		$this->_page_height = $formatarray['height'];
		$this->_format = array($this->_page_width,$this->_page_height);
		$this->_left_margin=10;
		$this->_right_margin=10;
		$this->_top_margin=10;
		$this->_bottom_margin=10;

		// get Sender
        $this->_sender=$mysoc;
        if (! $this->_sender->pays_code) $this->_sender->pays_code=substr($langs->defaultlang,-2);
	}


	/**
	 *	Function to generate license document on disk
	 *
	 *	@param	Object		$order   		Order to generate license doc for
	 *	@param	Translate	$outputlangs	Lang output object
	 *	@return	int         				1 if OK, <=0 if KO
	 */
	function write_file($order,$outputlangs)
	{
		global $user,$conf,$langs;
		$default_font_size = pdf_getPDFFontSize($outputlangs);

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output='ISO-8859-1';

		$outputlangs->load("main");
        $outputlangs->load("orders");
		$outputlangs->load("licensemanager@licensemanager");
		$outputlangs->load("companies");
		$outputlangs->load("products");

		$objectref = dol_sanitizeFileName($order->ref);
		$dir = $conf->commande->dir_output.'/'.$objectref;

		if (dol_mkdir($dir) >= 0)
		{
			$order->fetch_thirdparty();

			$langs->transnoentities("key") ? $fileIndication = "_" . $langs->transnoentities("key") :  $fileIndication = "_Key";

			$file = $dir . "/" . $objectref . $fileIndication . ".pdf";

			$pdf=pdf_getInstance($this->_format);

			if (class_exists('TCPDF'))
			{
				$pdf->setPrintHeader(false);
				$pdf->setPrintFooter(false);
			}
			$pdf->SetFont(pdf_getPDFFont($outputlangs));
			// Set path to the background PDF File
			if (empty($conf->global->MAIN_DISABLE_FPDI) && ! empty($conf->global->MAIN_ADD_PDF_BACKGROUND))
			{
				$pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
				$tplidx = $pdf->importPage(1);
			}

			$pdf->Open();

			$pagenb=0;
			$pdf->SetDrawColor(128,128,128);

			$pdf->SetTitle($outputlangs->convToOutputCharset($order->ref));
			$pdf->SetSubject($outputlangs->transnoentities("LicenseOrder"));
			$pdf->SetCreator("Dolibarr ".DOL_VERSION);
			$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
			$pdf->SetKeyWords($outputlangs->convToOutputCharset($order->ref)." ".$outputlangs->transnoentities("LicenseOrder"));
			if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION) $pdf->SetCompression(false);

			$pdf->SetMargins($this->_left_margin, $this->_top_margin, $this->_right_margin);   // Left, Top, Right
			$pdf->SetAutoPageBreak(1,0);

			// New page
			$pdf->AddPage();
			$pagenb++;
			$this->_pagehead($pdf, $order, 1, $outputlangs);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->MultiCell(0, 3, '');		// Set interline to 3
			$pdf->SetTextColor(0,0,0);

			$tab_top = 100;
			$tab_top_newpage = 50;
			$tab_height = 140;
			$tab_height_newpage = 190;

			$iniY = $tab_top + 7;
			$nexY = $tab_top + 7;

			// Complete object by loading several other informations
			$licenseOrderList = new Licenseorder($this->_db);
			$license = new License();

			if ($licenseOrderList->fetchList("fk_commande = $order->id","rowid ASC") > 0)
			{
				$i=0;
				$licenseCount = count($licenseOrderList->dataset);
				foreach($licenseOrderList->dataset as $data)
				{
					$license->key_mode=$data['key_mode'];
					$license->output_mode=$data['output_mode'];
					$license->code='';
					$nexY = $iniY;// one license per page

					$pdf->SetFont('','', $default_font_size - 1);   // Dans boucle pour gerer multi-page
					//Description of license order
					$licenseIdentification = '<table><tr><td><b>'.$outputlangs->trans('LicenseIdentification').'</b></td><td colspan="2">'.$data['identification'].'</td></tr></table>';
					$licenseNote = '<table><tr><td><b>'.$outputlangs->trans('LicenseNote').'</b></td><td colspan="2">'.$data['note'].'</td></tr></table>';

					$pdf->writeHTMLCell(150, 3, 30, $nexY, $outputlangs->convToOutputCharset($licenseIdentification), 1, 1, true);
					$nexY=$pdf->GetY();
					$pdf->writeHTMLCell(150, 3, 30, $nexY, $outputlangs->convToOutputCharset($licenseNote), 1, 1);
					$nexY=$pdf->GetY();

					$licenseOrderDetList = new Licenseorderdet($this->_db);
					$fk_license_order = $data['rowid'];
					if ($licenseOrderDetList->fetchList("fk_license_order = $fk_license_order","rowid ASC") > 0)
					{
						foreach ($licenseOrderDetList->dataset as $detData)
						{
							$detData['status'] = $data['status'];
							$nexY = $this->_licenseDetail($pdf,$detData,$license,30,$nexY,$outputlangs);
						}
					}

					$otherLicenseOrders = new Licenseorder($this->_db);
					if ($otherLicenseOrders->fetchList("fk_customer = $order->socid AND identification = '".$data['identification']."'") > 0)
					{
						foreach ($otherLicenseOrders->dataset as $otherData)
						{
							$otherLicenOrderId = $otherData['rowid'];
							if ($otherLicenOrderId < $data['rowid']) {
								$otherLicenseOrdersDet = new Licenseorderdet($this->_db);
								if ($otherLicenseOrdersDet->fetchList("fk_license_order = $otherLicenOrderId") > 0)
								{
									foreach ($otherLicenseOrdersDet->dataset as $detData)
									{
										$detData['status'] = $otherData['status'];
										$nexY = $this->_licenseDetail($pdf,$detData,$license,30,$nexY,$outputlangs);
									}
								}
							}
						}
					}

					if ($license->key_mode == 'multi')
					{
						// print multi license
						$nexY+=$this->_license($pdf,$license,30,$nexY,$outputlangs);
					}

					$this->_pagefoot($pdf, $order, $outputlangs);
					$i++;
					if ($i < $licenseCount) {
						// New page
						$pdf->AddPage();
						$pagenb++;
						$this->_pagehead($pdf, $order, 1, $outputlangs);
						$pdf->SetFont('','', $default_font_size - 1);
						$pdf->MultiCell(0, 3, '');		// Set interline to 3
						$pdf->SetTextColor(0,0,0);
					}
				}

				$this->_pagefoot($pdf, $order, $outputlangs);

				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file,'FD');
				if (! empty($conf->global->MAIN_UMASK))
					@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;
			}
		}
		else
		{
			$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
			return 0;
		}
	}

	/**
	 *   	Show footer of page. Need this->_sender object
     *
	 *   	@param	PDF			&$pdf     			PDF
	 * 		@param	Object		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @return	void
	 */
	function _pagefoot(&$pdf,$object,$outputlangs)
	{
		$pageFoot = pdf_pagefoot($pdf,$outputlangs,'DELIVERY_FREE_TEXT',$this->_sender,$this->_bottom_margin,$this->_left_margin,$this->_page_height,$object);
		$pdf->SetDrawColor(128,128,128);
	}

	/**
	 * print license key
	 *
	 * @param	TCPDF			&$pdf     		Object PDF
	 * @param object $license license containing output mode and code
	 * @param int $x x position
	 * @param int $y y position
	 * @param	Translate	$outputlangs		Object lang for output
	 *
	 * @return string html output of license key
	 */

	private function _license(&$pdf,$license,$x,$y,$outputlangs)
	{

		$licenseCode = '<table><tr><td><b>'.$outputlangs->trans('License').'</b></td><td colspan="2">'.$license->code.'</td></tr></table>';
		$pdf->writeHTMLCell(150,3,$x,$y, $outputlangs->convToOutputCharset($licenseCode), 1,1,true);
		$y = $pdf->getY()+7;
		if (!$license->output_mode ||($license->output_mode != 'text'))
		{
			if (Licensekeylist::is2d($license->output_mode))
			{
				$style = array('border'=>true,'padding'=>'auto','position'=>'C');
				$size = strlen($license->code)/3;
				$pdf->write2DBarcode($license->code,$license->output_mode,$x+50,$y,$size,$size,$style);
				$y = $y + $size + 7;
			}
			else
			{
				$style = array('border'=>true,'padding'=>'auto','position'=>'C');
				$pdf->write1DBarcode($license->code,$license->output_mode,$x,$y,150,20,$style);
				$y = $y + 20 + 7;
			}
		}
		return $y;
	}

	/**
	 *  Show top header of page.
	 *
	 *  @param	TCPDF		$pdf     		Object PDF
	 *  @param  Object		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @param	string		$titlekey		Translation key to show as title of document
	 *  @return	void
	 */
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs, $titlekey="License")
	{
		global $conf,$langs,$hookmanager;

		$outputlangs->load("main");
		$outputlangs->load("bills");
		$outputlangs->load("propal");
		$outputlangs->load("companies");
		$outputlangs->load("orders");
		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf,$outputlangs,$this->_page_height);

		// Show Draft Watermark
		if($object->statut==0 && (! empty($conf->global->COMMANDE_DRAFT_WATERMARK)) )
		{
            pdf_watermark($pdf,$outputlangs,$this->_page_height,$this->_page_width,'mm',$conf->global->COMMANDE_DRAFT_WATERMARK);
		}

		$pdf->SetTextColor(0,0,60);
		$pdf->SetFont('','B', $default_font_size + 3);

		$posy=$this->_top_margin;
		$posx=$this->_page_width-$this->_right_margin-100;

		$pdf->SetXY($this->_left_margin,$posy);

		// Logo
		$logo=$conf->mycompany->dir_output.'/logos/'.$this->_sender->logo;
		if ($this->_sender->logo)
		{
			if (is_readable($logo))
			{
			    $height=pdf_getHeightForLogo($logo);
			    $pdf->Image($logo, $this->_left_margin, $posy, 0, $height);	// width=0 (auto)
			}
			else
			{
				$pdf->SetTextColor(200,0,0);
				$pdf->SetFont('','B', $default_font_size -2);
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'L');
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToGlobalSetup"), 0, 'L');
			}
		}
		else
		{
			$text=$this->_sender->name;
			$pdf->MultiCell(100, 4, $outputlangs->convToOutputCharset($text), 0, 'L');
		}

		$pdf->SetFont('','B', $default_font_size + 3);
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColor(0,0,60);
		$title=$outputlangs->transnoentities($titlekey);
		$pdf->MultiCell(100, 3, $title, '', 'R');

		$pdf->SetFont('','B',$default_font_size);

		$posy+=5;
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColor(0,0,60);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("Ref")." : " . $outputlangs->convToOutputCharset($object->ref), '', 'R');

		$posy+=1;
		$pdf->SetFont('','', $default_font_size - 1);

		if ($object->ref_client)
		{
			$posy+=5;
			$pdf->SetXY($posx,$posy);
			$pdf->SetTextColor(0,0,60);
			$pdf->MultiCell(100, 3, $outputlangs->transnoentities("RefCustomer")." : " . $outputlangs->convToOutputCharset($object->ref_client), '', 'R');
		}

		$posy+=4;
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColor(0,0,60);
		$pdf->MultiCell(100, 3, $outputlangs->transnoentities("OrderDate")." : " . dol_print_date($object->date,"%d %b %Y",false,$outputlangs,true), '', 'R');

		// Get contact
		if (!empty($conf->global->DOC_SHOW_FIRST_SALES_REP))
		{
		    $arrayidcontact=$object->getIdContact('internal','SALESREPFOLL');
		    if (count($arrayidcontact) > 0)
		    {
		        $usertmp=new User($this->db);
		        $usertmp->fetch($arrayidcontact[0]);
		        $pdf->SetTextColor(0,0,60);
		        $pdf->MultiCell(190, 3, $langs->trans("SalesRepresentative")." : ".$usertmp->getFullName($langs), '', 'R');
		    }
		}

		$posy+=2;

		// Show list of linked objects
		$posy = pdf_writeLinkedObjects($pdf, $object, $outputlangs, $posx, $posy, 100, 3, 'R', $default_font_size);

		if ($showaddress)
		{
			// Sender properties
			$carac_emetteur = pdf_build_address($outputlangs, $this->_sender, $object->thirdparty);

			// Show sender
			$posy=42;
			$posx=$this->_left_margin;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx=$this->_page_width-$this->_right_margin-80;
			$hautcadre=40;

			// Show sender frame
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($posx,$posy-5);
			$pdf->MultiCell(66,5, $outputlangs->transnoentities("BillFrom").":", 0, 'L');
			$pdf->SetXY($posx,$posy);
			$pdf->SetFillColor(230,230,230);
			$pdf->MultiCell(82, $hautcadre, "", 0, 'R', 1);
			$pdf->SetTextColor(0,0,60);

			// Show sender name
			$pdf->SetXY($posx+2,$posy+3);
			$pdf->SetFont('','B', $default_font_size);
			$pdf->MultiCell(80, 4, $outputlangs->convToOutputCharset($this->_sender->name), 0, 'L');
			$posy=$pdf->getY();

			// Show sender information
			$pdf->SetXY($posx+2,$posy);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->MultiCell(80, 4, $carac_emetteur, 0, 'L');



			// If CUSTOMER contact defined on order, we use it
			$usecontact=false;
			$arrayidcontact=$object->getIdContact('external','CUSTOMER');
			if (count($arrayidcontact) > 0)
			{
				$usecontact=true;
				$result=$object->fetch_contact($arrayidcontact[0]);
			}

			//Recipient name
			// On peut utiliser le nom de la societe du contact
			if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) {
				$thirdparty = $object->contact;
			} else {
				$thirdparty = $object->thirdparty;
			}

			$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);

			$carac_client=pdf_build_address($outputlangs,$this->_sender,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target', $object);

			// Show recipient
			$widthrecbox=100;
			if ($this->_page_width < 210) $widthrecbox=84;	// To work with US executive format
			$posy=42;
			$posx=$this->_page_width-$this->_right_margin-$widthrecbox;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx=$this->_left_margin;

			// Show recipient frame
			$pdf->SetTextColor(0,0,0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($posx+2,$posy-5);
			$pdf->MultiCell($widthrecbox, 5, $outputlangs->transnoentities("BillTo").":",0,'L');
			$pdf->Rect($posx, $posy, $widthrecbox, $hautcadre);

			// Show recipient name
			$pdf->SetXY($posx+2,$posy+3);
			$pdf->SetFont('','B', $default_font_size);
			$pdf->MultiCell($widthrecbox, 4, $carac_client_name, 0, 'L');

			$posy = $pdf->getY();

			// Show recipient information
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetXY($posx+2,$posy);
			$pdf->MultiCell($widthrecbox, 4, $carac_client, 0, 'L');
		}

		$pdf->SetTextColor(0,0,0);
	}

	/**
	 * print license list
	 *
	 * @param PDF			&$pdf     			PDF
	 * @param array $data licenseorderdet data
	 * @param License &$license license reference to append multi licenses into
	 * @param int $x x position
	 * @param int $y y position
	 * @param	Translate	$outputlangs		Object lang for output
	 *
	 * @return int $y y increment
	 */
	private function _licenseDetail(&$pdf,$data,&$license,$x,$y,$outputlangs) {
		$licenseProduct = new Licenseproduct($this->_db);
		$licenseKeylist = new Licensekeylist($this->_db);
		$product = new Product($this->_db);
		$keyTypes = $licenseKeylist->getKeyTypes();

		if ($licenseProduct->fetch($data['fk_license_product'])> 0)
		{
			if ($licenseKeylist->fetch($licenseProduct->fk_base_key) > 0)
			{
				//product
				$product->fetch($licenseProduct->fk_product);
				$licenseProduct = '<table><tr><td><b>'.$outputlangs->trans('Product').'</b></td><td>'.$product->ref.'</td><td>'.$product->label.'</td></tr></table>';
				$pdf->writeHTMLCell(150, 3, $x, $y, $outputlangs->convToOutputCharset($licenseProduct), 1, 1,true);
				$y=$pdf->getY();
				//Date create
				$licenseBuy = '<table><tr><td><b>'.$outputlangs->trans('LicenseBuy').'</b></td><td colspan="2">'.dol_print_date($data['datec'],'daytext').'</td></tr></table>';
				$pdf->writeHTMLCell(150, 3, $x, $y, $outputlangs->convToOutputCharset($licenseBuy), 1, 1);
				$y=$pdf->getY();
				//Date expire
				if ($data['status'] == Licenseorder::STATUS_CANCELED) {
					// print expired license
					$licenseExpire = '<table><tr><td><b>'.$outputlangs->trans('LicenseExpired').'</b></td><td colspan="2">'.dol_print_date($data['datev'],'daytext').'</td></tr></table>';
				} elseif ($data['status'] == Licenseorder::STATUS_DRAFT) {
					// print draft license
					$licenseExpire = '<table><tr><td><b>'.$outputlangs->trans('LicenseDraft').'</b></td><td colspan="2">'.dol_print_date($data['datev'],'daytext').'</td></tr></table>';
				} else {
					$licenseExpire = '<table><tr><td><b>'.$outputlangs->trans('LicenseExpire').'</b></td><td colspan="2">'.dol_print_date($data['datev'],'daytext').'</td></tr></table>';
				}
				$pdf->writeHTMLCell(150, 3, $x, $y, $outputlangs->convToOutputCharset($licenseExpire), 1, 1);
				$y=$pdf->getY();

				// print Licensekey when it is a single license else append license

				if ($license->key_mode == 'multi') {
					if ($data['status'] == Licenseorder::STATUS_VALIDATED) {
						if ($license->code) $license->code .= $licenseKeylist->multi_key_separator;
						$license->code .= $data['license_key'];
					}
				} else {
					$license->code = $data['license_key'];
					$y+=$this->_license($pdf, $license, $x, $y,$outputlangs);
				}
			}
		}
		return $y;
	}
}

?>
