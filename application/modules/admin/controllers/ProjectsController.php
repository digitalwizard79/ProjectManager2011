<?php

class Admin_ProjectsController extends Zend_Controller_Action
{
	public function indexAction()
	{
		$p	= new Projects();
		$this->view->projects	= $p->fetchAllProjects();
	}

	public function init()
	{
		$this->_helper->layout->setLayout('admin-layout');
	}

    public function setdeptAction()
    {
        $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

        $department           = $_POST['value'];
        list($junk, $id)    = explode('_', $_POST['id']);

        $projects   = new Projects();
        $project    = $projects->find($id)->current();

        $project->department  = $department;
        $project->save();

		echo $department;
    }

    public function setpriorityAction()
    {
        $this->_helper->layout->disableLayout();
		$this->_helper->viewRenderer->setNoRender(true);

        $priority           = (int) $_POST['value'];
        list($junk, $id)    = explode('_', $_POST['id']);

        $projects   = new Projects();
        $project    = $projects->find($id)->current();

        $project->priority  = $priority;
        $project->save();

		echo $priority;
    }
}
