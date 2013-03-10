<?php

class Admin_PendingController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$projects	= new Projects();
		$pending	= $projects->fetchUnapprovedProjects();

		$this->view->projects	= $pending;
	}

	public function init()
	{
		$this->_helper->layout->setLayout('admin-layout');
	}

	public function viewAction()
	{
		$projects	= new Projects();
		$project	= $projects->fetchById($this->_request->getParam('project'));
		$rois		= new ProjectROI();
		$roi		= $rois->fetchRow($rois->select()->where('projectId = ?', $this->_request->getParam('project')));

		$this->view->assign($project->toArray());
		$this->view->analysis	= $roi->description;
	}

	public function updateAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		$projects	= new Projects();
		$action		= $this->_request->getParam('newStatus');
		$pid		= $this->_request->getParam('pid');
		$notes		= $this->_request->getParam('notes');
		switch($action) {
			case 'approve':
				$projects->approve($pid, $notes);
				break;
			case 'decline':
				$projects->decline($pid, $notes);
				break;
			case 'refer':
				$projects->refer($pid, $notes);
				break;
		}

		echo json_encode(array('status' => 1));
	}
}