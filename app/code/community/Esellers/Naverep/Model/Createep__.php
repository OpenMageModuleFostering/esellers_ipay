<?php
	class Esellers_Naverep_Model_Createep extends Mage_Core_Model_Abstract
	{
            var $fp;
            
            public function makeEp() {
                $productIds=$this->getProductIds(1);
                
                $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*')->addIdFilter($productIds);
                
                $baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();   
                $currentCurrencyCode = Mage::app()->getStore()->getCurrentCurrencyCode();  
                $shipfee = (int)Mage::helper('directory')->currencyConvert($this->getAllShippingPrice(), $baseCurrencyCode, $currentCurrencyCode);
                
                //$fp = fopen(Mage::getBaseDir()."/ep.txt","w+");
                $data   =   "<<<begin>>>\n";

                foreach ($collection as $product){
                    $price = (int)Mage::helper('directory')->currencyConvert($product->getPrice(), $baseCurrencyCode, $currentCurrencyCode);
                    
                    $imgurl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).'catalog/product'.$product->getImage();
                    $pgurl  = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK).$product->getUrlPath();   
                    $data   .=  "<<<mapid>>>".$product->getSku()."\n";
                    $data   .=  "<<<pname>>>".$product->getName()."\n";
                    $data   .=  "<<<price>>>".$price."\n";
                    $data   .=  "<<<pgurl>>>".$pgurl."\n";
                    $data   .=  "<<<mourl>>>".$pgurl."\n";
                    $data   .=  "<<<igurl>>>".$imgurl."\n";
                    
                    foreach ($product->getCategoryIds() as $category_id) { 
                        $category = Mage::getModel('catalog/category')->load($category_id);
                        $data.= $this->getCategoryData($category_id,"",$category->getLevel());                       
                    }  
                    
                    $data   .=  "<<<model>>>".$product->getModel()."\n";
                    //$data   .=  "<<<brand>>>".$product->getAttributeText('computer_manufacturers')."\n";
                    $data   .=  "<<<maker>>>".$product->getAttributeText('manufacturer')."\n";
                    $data   .=  "<<<origi>>>".$product->getAttributeText('country_of_manufacture')."\n";
                    $data   .=  "<<<deliv>>>$shipfee\n";
                    $data   .=  "<<<event>>>\n";
                }
                $data   .=  "<<<ftend>>>\n";
                header("Cache-Control: no-cache, must-revalidate");
                header("Content-Type: text/plain; charset=euc-kr");
                echo $data;
                //fwrite($fp,$data);
                //fclose($fp);
            }
            
            public function getCategoryData($category_id,$data,$initLevel){
                $category = Mage::getModel('catalog/category')->load($category_id);
                $level = $category->getLevel();
                if($level>1){
                    $data="<<<cate".($level-1).">>>".$category->getName()."\n".$data;
                    $data="<<<caid".($level-1).">>>".$category_id."\n".$data;
                    
                    return $this->getCategoryData($category->getParentId(),$data,$initLevel);
                }else{
                    for($i=$initLevel;$i<5;$i++){
                        $data.="<<<cate".($i).">>>\n";
                        $data.="<<<caid".($i).">>>\n";
                    }
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

                
                header("Cache-Control: no-cache, must-revalidate");
                header("Content-Type: text/plain; charset=euc-kr");
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
                        $data.= $this->getCategoryData($category_id,"",$category->getLevel());                       
                    }  
                    $data   .=  "<<<model>>>".$product->getModel()."\n";
                    //$data   .=  "<<<brand>>>".$product->getAttributeText('computer_manufacturers')."\n";
                    $data   .=  "<<<maker>>>".$product->getAttributeText('manufacturer')."\n";
                    $data   .=  "<<<origi>>>".$product->getAttributeText('country_of_manufacture')."\n";
                    $data   .=  "<<<deliv>>>$shipfee\n";

                    $data   .="<<<utime>>>".$product->getUpdatedAt()."\n";
                    
                    if($mode=="in"){
                        $data   .="<<<class>>U\n";
                    }else if($mode=="out"){
                        $data   .="<<<class>>D\n";
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