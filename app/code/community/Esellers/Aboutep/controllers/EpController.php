<?php
class Esellers_Aboutep_EpController extends Mage_Core_Controller_Front_Action {
      
    public function indexAction()
    {
        $model=Mage::getModel("aboutep/createep");
        $model->makeEp();
        //$this->loadLayout();
        //$this->renderLayout();
    }

    public function summaryAction()
    {
        $model=Mage::getModel("aboutep/createep");
        $model->makeSummaryep();     
    }
}