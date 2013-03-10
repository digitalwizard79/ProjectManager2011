<?php

class ProjectsController extends Zend_Controller_Action
{
	public function init()
	{
		$this->_helper->layout->setLayout('project-layout');
	}

	public function addAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		if($_POST) {
			$projectName	= $this->_request->getParam('projectName');
			$description	= $this->_request->getParam('projectDescription');

			try {
				$projects	= new Projects();
				$projects->add(array('name' => $projectName, 'description' => $description));
				$status		= 1;
				$message	= 'Project was added successfully.';
			} catch (Exception $e) {
				$status		= 0;
				$message	= $e->getMessage();
			}

			echo json_encode(array('status' => $status, 'message' => $message));
		}
	}

	public function completeAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		$projectId	= $this->_request->getParam('pid');
		$projects	= new Projects();

		$status		= 0;
		$message	= 'Unknown error has occurred';

		if($projects->complete($projectId)) {
			$status		= 1;
			$message	= 'Project has been closed.';
		} else {
			$message	= 'Project was not able to be closed';
		}

		echo json_encode(array('status' => $status, 'message' => $message));
	}

	public function loadAction()
	{
		$projectId	= $this->_request->getParam('project');
		$user		= Zend_Auth::getInstance()->getIdentity();
		$p			= new Projects();
		$u			= new Users();

		if( ($p->isMember($user->id, $projectId)) || ($u->isAdmin($user->primaryGroup)) ) {
			$_SESSION['projectId']	= $projectId;
			$project	= $p->find($projectId)->current();
			$_SESSION['projectApproved']	= $project->approved;

			switch($project->status) {
				case 1:
					$controller	= 'roi';
					break;
				case 2:
					$controller	= 'scope';
					break;
				case 3:
					$controller	= 'dates';
					break;
				case 4:
					$controller	= 'implementation';
					break;
				case 5:
					$controller	= 'alpha';
					break;
				case 6:
					$controller	= 'beta';
					break;
				case 7:
					$controller	= 'documentation';
					break;
				case 8:
					$controller	= 'deployment';
					break;
				case 9:
					$controller	= 'maintenance';
					break;
				default:
					$controller	= 'roi';
					break;
			}

			$this->_forward('index', $controller, 'default');
		} else {
			$this->_forward('index', 'index', 'default');
		}
	}
}