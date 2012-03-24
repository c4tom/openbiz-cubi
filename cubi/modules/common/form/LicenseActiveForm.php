<?php 
require_once "LicenseForm.php";
class LicenseActiveForm extends LicenseForm
{
	public $m_ActiveModuleName;
	
 	public function setSessionVars($sessionContext)
    {               
	 	$sessionContext->setObjVar("common.LicenseForm", "ActiveModuleName", $this->m_ActiveModuleName);       
     	parent::setSessionVars($sessionContext);        
    }	
	
	public function getSessionVars($sessionContext)
    {
        $sessionContext->getObjVar("common.LicenseForm", "ActiveModuleName", $this->m_ActiveModuleName);
     	parent::getSessionVars($sessionContext);        
    }	
	
	public function fetchData()
	{
		var_dump($_SESSION);exit;
		$this->m_ActiveModuleName = $_GET['app'];
		$this->m_ModuleName = $_GET['app'];
		$result['license_code']=$this->getExistingLicenseCode();
		$this->getAppRegister();		
		return $result;
	}
	
	public function activeLicense()
	{		
		$rec = $this->readInputRecord();
		$lic_code = $rec['license_code'];
		$this->setLicenseCode($lic_code);
		return;
	}
	
	public function getExistingLicenseCode()
	{
		$lic_file = MODULE_PATH.DIRECTORY_SEPARATOR.$this->m_ActiveModuleName.DIRECTORY_SEPARATOR.'license.key';
		if(file_exists($lic_file))
		{
			return file_get_contents($lic_file);
		}
	}	
	
	public function setLicenseCode($code)
	{
		$lic_file = MODULE_PATH.DIRECTORY_SEPARATOR.$this->m_ActiveModuleName.DIRECTORY_SEPARATOR.'license.key';	
		return file_put_contents($lic_file,$code);
	}
}
?>