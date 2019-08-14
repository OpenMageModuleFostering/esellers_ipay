<?php
	class Esellers_Aboutep_Model_Createep extends Mage_Core_Model_Abstract
	{
            var $fp;
            
            public function makeEp() {
                $productIds=$this->getProductIds(1);
                $data = "";
                $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')->addIdFilter($productIds);
                
                $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();   
                $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();  
                $shipfee = (int)Mage::helper('directory')->currencyConvert($this->getAllShippingPrice(), $baseCurrencyCode, $currentCurrencyCode);
                
                
                Mage::log("start");
                foreach ($collection as $product){
                    $price = (int)Mage::helper('directory')->currencyConvert($product->getPrice(), $baseCurrencyCode, $currentCurrencyCode);
                    $imgurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
                    $pgurl  = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK).$product->getUrlPath();   
                    $data   .=  $product->getSku()."<!>";
                    $data   .=  "C<!>";
                    $data   .=  $product->getName()."<!>";
                    $data   .=  $price."<!>";
                    $data   .=  $imgurl."<!>";
                    $data   .=  $pgurl."<!>";
                    foreach ($product->getCategoryIds() as $category_id) { 
                        $category = Mage::getModel('catalog/category')->load($category_id);
                        $data.= $this->getCategoryData($category_id,"",$category->getLevel())."<!>"; 
                    }
                    
                    $data   .=  $product->getModel()."<!>";
                    $data   .=  $product->getBrand()."<!>";
                    $data   .=  $product->getAttributeText('manufacturer')."<!>";
                    $data   .=  $product->getAttributeText('country_of_manufacture')."<!>";
                    $data   .=  $product->getCreateDt()."<!>";
                    $data   .=  $shipfee."<!>";
                    $data   .=  "<!>";          //이벤트
                    $data   .=  "<!>";          //쿠폰금액
                    $data   .=  "<!>";          //무이자
                    $data   .=  "<!>";          //적립금
                    $data   .=  "<!>";          //이미지 변경여부
                    $data   .=  "<!>";          //물품특성정보
                    $data   .=  "<!>";          //상점내 매출비율
                    $data   .=  "<!>";          //특별할인카드명
                    $data   .=  "<!>";          //특별할인 할인 금액
                    $data   .=  "<!>";          //상품정보 변경시간
                    
                    $data   .=  "\n";
                }
     Mage::log("end");
     
                header("Cache-Control: no-cache, must-revalidate");
                header("Content-Type: text/plain; charset=euc-kr");     
                echo $data;
            }
            
            public function getCategoryData($category_id,$data,$cate){
                $category = Mage::getModel('catalog/category')->load($category_id);
                $level = $category->getLevel();
                $cate=$category_id.$cate;
                if($level>2){
                    if($data=="")
                        $data=$category_id.$data."<!>";
                    else
                        $data=$cate."<i>".$category_id.$data;
                    return $this->getCategoryData($category->getParentId(),$data,$cate);
                }else{
                    $data=$category_id."<!>".$data;
                    return $data;
                }
            }

            public function makeSummaryep() {
                $data   =   "<<<begin>>>\n";      
                $productIds=$this->getProductIds(1);
                $nowtime=date("Y-m-d", time()-86400)." 08:00:00";
                $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')->addFieldToFilter("updated_at",array("gt"=>"$nowtime"))->addIdFilter($productIds);
                
                $data   .=  $this->makeTxt("in",$collection);

                
                $productIds_out=$this->getProductIds(0);
                
                $collection_out = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')->addIdFilter($productIds_out);
                $data   .=  $this->makeTxt("out",$collection_out);
                
                
                $data   .=  "<<<ftend>>>\n";

                
                echo $data;
            }
            
            public function getProductIds($status){
                $stockCollection = Mage::getModel('cataloginventory/stock_item')->getCollection()->addFieldToFilter('is_in_stock',$status);
                $productIds = array();

                foreach ($stockCollection as $item){
                    $productIds[] = $item->getOrigData('product_id');
                }                                
                return $productIds;
            }
            
            public function makeTxt($mode,$collection){
                $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();   
                $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();  
                $shipfee = (int)Mage::helper('directory')->currencyConvert($this->getAllShippingPrice(), $baseCurrencyCode, $currentCurrencyCode);
                $data="";
                
                foreach ($collection as $product){
                    $price = (int)Mage::helper('directory')->currencyConvert($product->getPrice(), $baseCurrencyCode, $currentCurrencyCode);
                    $imgurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
    
                    $data   .=  "<<<mapid>>>".$product->getSku()."\n";
                    $data   .=  "<<<pname>>>".$product->getName()."\n";
                    $data   .=  "<<<price>>>".$price."\n";
                    $data   .=  "<<<pgurl>>>".$product->getProductUrl()."\n";
                    $data   .=  "<<<igurl>>>".$imgurl."\n";
                    
                    foreach ($product->getCategoryIds() as $category_id) { 
                        $category = Mage::getModel('catalog/category')->load($category_id);
                        $data.= $this->getCategoryData($category_id,"","");                       
                    }  
                    $data   .=  "<<<model>>>".$product->getModel()."\n";
                    //$data   .=  "<<<brand>>>".$product->getAttributeText('computer_manufacturers')."\n";
                    $data   .=  "<<<maker>>>".$product->getAttributeText('manufacturer')."\n";
                    $data   .=  "<<<origi>>>".$product->getAttributeText('country_of_manufacture')."\n";
                    $data   .=  "<<<deliv>>>$shipfee\n";

                    $data   .=" <<<utime>>>".$product->getUpdatedAt()."\n";
                    
                    if($mode=="in"){
                        $data   .=" <<<class>>U\n";
                    }else if($mode=="out"){
                        $data   .=" <<<class>>D\n";
                    }
                    
                    $data   .=  "<<<event>>>\n";
                    
                }

                
                return $data;
               
            }
            
            public function getAllShippingPrice()
            {
                $store=Mage::app()->getStore()->getId();
                $carriers = Mage::getStoreConfig('carriers', $store);
                foreach ($carriers as $carrierCode => $carrierConfig) {
                    if (Mage::getStoreConfigFlag('carriers/'.$carrierCode.'/active', $store)) {
                        if($carrierCode=="flatrate"){
                            return $carrierConfig["price"];
                        }
                     }
                }
                return 0;
            }
	}
?>