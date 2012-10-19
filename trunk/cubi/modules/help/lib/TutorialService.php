<?php 
class TutorialService 
{	
	const SESSION_VAR_NAME			= "HELP_TUTORAIL_SHOWN";
	
	protected $m_TutorialDO 		= "help.tutorial.do.TutorialDO";
	protected $m_TutorialUserDO 	= "help.tutorial.do.TutorialUserDO";
	protected $m_TutorialForm 		= "help.tutorial.widget.TutorialForm";	
	
	public function AutoShowTutorial($url,$formObj)
	{
		$tutorialRec = BizSystem::getObject($this->m_TutorialDO)->fetchOne("[url_match]='$url'");
		$tutorialId = $tutorialRec['Id'];
		if(!$tutorialId)
		{
			return false;
		}
		if($this->_checkNeedShowTutorial($tutorialId))
		{
			//show the form		
			$formObj->loadDialog($this->m_TutorialForm,$tutorialId);
			
			//set it has been shown
			//$this->_setTutorialShown($tutorialId);
		}
		return true;
	}

	protected function _checkNeedShowTutorial($tutorialId)
	{
		$tutorialShown = BizSystem::sessionContext()->getvar(self::SESSION_VAR_NAME);
		if($tutorialShown[$tutorialId])
		{
			return false;
		}
		$userId = BizSystem::getUserProfile("Id");
		$showLog = BizSystem::getObject($this->m_TutorialUserDO)->fetchOne("[tutorial_id]='$tutorialId' AND [user_id]='$userId'");
		if(!$showLog)
		{
			return true;
		}
		else 
		{
			if($showLog['autoshow']==1)
			{
				return true;
			}
			else 
			{
				return false;
			}
		}
	}
	
	public function SetTutorialShown($tutorialId,$showOnNextLogin)
	{
		$tutorialShown = BizSystem::sessionContext()->getvar(self::SESSION_VAR_NAME);
		$tutorialShown[$tutorialId]=true;				
		BizSystem::sessionContext()->setVar(self::SESSION_VAR_NAME,$tutorialShown);
		$userId = BizSystem::getUserProfile("Id");
		if(!BizSystem::getObject($this->m_TutorialUserDO)->fetchOne("[tutorial_id]='$tutorialId' AND [user_id]='$userId'"))
		{
			$rec = array(
				"tutorial_id" => $tutorialId,
				"user_id"	  => $userId,
				"autoshow"	  => $showOnNextLogin
			);
			BizSystem::getObject($this->m_TutorialUserDO)->insertRecord($rec);
		}
		return true;
	}
}
?>