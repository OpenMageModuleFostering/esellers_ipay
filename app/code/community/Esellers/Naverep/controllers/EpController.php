<?php
class Esellers_Naverep_EpController extends Mage_Core_Controller_Front_Action {
      
    public function indexAction()
    {
        $model=Mage::getModel("naverep/createep");
        $model->makeEp();
        //$this->loadLayout();
        //$this->renderLayout();
    }

    public function summaryAction()
    {
        $model=Mage::getModel("naverep/createep");
        $model->makeSummaryep();     
    }
}