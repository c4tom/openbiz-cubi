<?php
class OauthProviderForm extends EasyForm
{
	protected $m_type;
	protected $m_key;
	protected $m_secret;

	public function testAllProvider()
	{
		
	}
	
	public function TestProvider($RecType=false)
	{	
		if(!$this->m_type)
		{
			$Record=$this->getActiveRecord();
			$this->m_type=$Record['type'];
			$this->m_key=$Record['key'];
			$this->m_secret=$Record['value'];
		}
		$oatuthType=MODULE_PATH."/oauth/libs/{$this->m_type}.class.php";
		if(!file_exists($oatuthType))
		{
			throw new Exception('Unknown type');
			return;
		}
		$whitelist_arr=array('qq','sina','alipay','google','facebook');
		if(!in_array($this->m_type,$whitelist_arr)){
			throw new Exception('Unknown service');
			return;
		}
		include_once $oatuthType;
		$obj = new $this->m_type;

		$rec_arr=$obj->test($this->m_key,$this->m_secret);
		if($rec_arr['oauth_token'])
		{
			$this->m_Notices = array("test"=>$this->getMessage("TEST_SUCCESS"));
		}
		else
		{
			$this->m_Errors = array("test"=>$this->getMessage("TEST_FAILURE"));

		}
	
		if($RecType)
		{
			if($this->m_Errors)
			{
				BizSystem::ClientProxy()->showClientAlert($this->getMessage("TEST_FAILURE"));
				return false;
			}
			else
			{
				return true;
			}	
			
		}
		else
		{
			$this->rerender();
		}
		return true;
	}
	
	public function UpdateRecord(){
		$this->m_type = BizSystem::ClientProxy()->getFormInputs("fld_type");
		$this->m_key= BizSystem::ClientProxy()->getFormInputs("fld_key");	
		$this->m_secret = BizSystem::ClientProxy()->getFormInputs("fld_value");	
		if($this->TestProvider(true))
		{
			parent::UpdateRecord();
		}
	}
}
?>