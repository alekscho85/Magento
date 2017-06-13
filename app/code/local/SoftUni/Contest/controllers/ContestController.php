<?php


class SoftUni_Contest_ContestController extends Mage_Core_Controller_Front_Action
{
    public function listAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}