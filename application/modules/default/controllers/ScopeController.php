<?php

class ScopeController extends Zend_Controller_Action
{
	protected $_step	= 2;

	public function indexAction()
	{
		$projectId	= $_SESSION['projectId'];

		$projects	= new Projects();
		$sdds		= new ProjectScopes();
		$sdd		= $sdds->fetchAll($sdds->select()->where('projectId = ?', $projectId));
		$project	= $projects->find($projectId)->current();

		if(count($sdd) == 0) {
			$sdd	= $sdds->createRow();
			$sdd->projectId = $projectId;
			$sdd->save();
		} elseif(count($sdd) == 1) {
			$sdd	= $sdd[0];
		}

		$this->view->projectName	= $project->name;
		$this->view->projectId		= $projectId;
		$this->view->assign($sdd->toArray());
	}

	public function init()
	{
		$this->_helper->layout->setLayout('project-layout');		

		if($_SESSION['projectApproved'] != 1) {
			$this->_helper->redirector('load', 'projects', 'default', array('project' => $_SESSION['projectId']));
		}
	}

	public function saveAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		if($_POST) {
			$section	= $this->_request->getParam('section');
			$text		= $this->_request->getParam('value');
			$projectId	= $_SESSION['projectId'];
			$sdds		= new ProjectScopes();
			$sdd		= $sdds->fetchRow($sdds->select()->where('projectId = ?', $projectId));

			$sdd->{$section}	= $text;
			$sdd->save();

			echo $text;
		}
	}
}