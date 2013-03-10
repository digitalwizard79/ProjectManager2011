<?php

class Admin_ReportsController extends Zend_Controller_Action
{
	public function indexAction()
	{}

	public function init()
	{
		$this->_helper->layout->setLayout('admin-layout');
	}

	public function pbuAction()
	{
		$users	= new Users();
		$select	= $users->select()->from(array('u' => 'u_Users'), array('id', 'name', 'username'))
								  ->join(array('up' => 'up_UserProjects'), 'u.id = up.userId')
								  ->join(array('p' => 'p_Projects'), 'p.id = up.projectId', array('projectName' => 'name'))
								  ->where('u.status = 1')
								  ->order('u.name ASC')
								  ->order('p.name ASC')
								  ->setIntegrityCheck(false);
								  
		$result	= $users->fetchAll($select);
		$this->view->results = $result;
	}

    public function plwdAction()
    {
        $projects   = new Projects();
        $select     = $projects->select()->from(array('p' => 'p_Projects'), array('name', 'description', 'priority', 'department'))
                                         ->join(array('u' => 'u_Users'), 'p.author = u.id', array('authorName' => 'name'))
                                         ->where('p.approved != -1')
										 ->order('p.department')
                                         ->order('p.name')
                                         ->setIntegrityCheck(false);

        $results = $projects->fetchAll($select);
        $this->view->results    = $results;
    }
}
