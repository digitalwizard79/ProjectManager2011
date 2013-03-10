<?php

class DatesController extends Zend_Controller_Action
{
	protected $_step	= 3;

	public function addtaskAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		if($_POST) {
			$data	= array(
				'milestone'		=> $this->_request->getParam('milestone'),
				'description'	=> $this->_request->getParam('description'),
				'dateTarget'	=> date('Y-m-d h:i:s', strtotime($this->_request->getParam('dateTarget'))),
				'author'		=> Zend_Auth::getInstance()->getIdentity()->id,
				'projectId'		=> $_SESSION['projectId'],
			);

			try {
				$dates		= new TargetDates();
				$dates->add($data);
				$status		= 1;
				$message	= 'Target Date was added successfully.';
			} catch (Exception $e) {
				$status		= 0;
				$message	= $e->getMessage();
			}

			echo json_encode(array('status' => $status, 'message' => $message));
		}
	}

	public function indexAction()
	{
		$projectId	= $_SESSION['projectId'];
		$tasks		= new TargetDates();
		$select		= $tasks->select()->where('projectId = ?', $projectId)->order(array('dateTarget ASC', 'milestone ASC'));
		$this->view->tasks	= $tasks->fetchAll($select);
	}

	public function init()
	{
		$this->_helper->layout->setLayout('project-layout');

		if($_SESSION['projectApproved'] != 1) {
			$this->_helper->redirector('load', 'projects', 'default', array('project' => $_SESSION['projectId']));
		}
	}

	public function updatecellAction()
	{
		$this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

		if($_POST) {
			$element			= $this->_request->getParam('id');
			list($section, $id)	= explode('_', $element);
			$text				= $this->_request->getParam('value');

			$tasks		= new TargetDates();
			$task		= $tasks->fetchRow($tasks->select()->where('id = ?', $id));

			switch($section) {
				case 'dateTarget':
					$text	= date('Y-m-d h:i:s', strtotime($text));
					break;
			}
			$task->{$section}	= $text;
			$task->save();

			echo $text;
		}
	}
}