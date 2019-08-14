<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    design
 * @package     base_default
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
?>
<?php
class ipaySession
{		
	private $serverUrl;
	private $soapAuction;
	
	public function __construct($serverUrl, $soapAuction)
	{
		$this->serverUrl = $serverUrl;
		$this->soapAuction = $soapAuction;	
	}
	
	/**	sendHttpRequest
		Sends a HTTP request to the server for this session
		Input:	$requestBody
		Output:	The HTTP Response as a String
	*/
	public function sendHttpRequest($requestBody)
	{
		//build auction headers using variables passed via constructor
		$headers = $this->buildAuctionHeaders(strlen($requestBody));
				
		//initialise a CURL session
		$connection = curl_init();
		
				 
		//set the server we are using (could be Sandbox or Production server)
		curl_setopt($connection, CURLOPT_URL, $this->serverUrl);
		
		//stop CURL from verifying the peer's certificate
		curl_setopt($connection, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($connection, CURLOPT_SSL_VERIFYHOST, 0);
		
		//set the headers using the array of headers
		curl_setopt($connection, CURLOPT_HTTPHEADER, $headers);
		
		//set method as POST
		curl_setopt($connection, CURLOPT_POST, 1);
		
		//set the XML body of the request
		curl_setopt($connection, CURLOPT_POSTFIELDS, $requestBody);
		
		//set it to return the transfer as a string from curl_exec
		curl_setopt($connection, CURLOPT_RETURNTRANSFER, 1);
		
		//Send the Request
		$response = curl_exec($connection);
		
		//close the connection
		curl_close($connection);
		
		//return the response
		return $response;
	}
	
	private function buildAuctionHeaders($requestBodyLength)
	{
		$headers = array (
			"Content-Type: text/xml; charset=utf-8",
			"Content-Length: $requestBodyLength",
			"SOAPAction: $this->soapAuction"
		);
		
		return $headers;
	}
}
 /*
 * requestOrderNo
 * 상품정보를 전달하고 발급된 카트번호를 요청합니다.
 * http://www.auction.co.kr/IpayService/Ipay/InsertIpayOrder  
 * 서비스 문의시에 Request SOAP과 Response SOAP을 보내주시면 됩니다.
 * 옥션 API 개발자 커뮤니티 : http://api.auction.co.kr/developer
 */
class requestOrderNo
{ 	  	 
	private $serverUrl = "https://api.auction.co.kr/ArcheSystem/IpayService.asmx";	//실제 운영 서버 주소
	private $action = "http://www.auction.co.kr/IpayService/Ipay/IpayDenySell";  
	private $ticket; 
        private $auction_id;
	
	
	public function __construct($ticket,$auction_id){ 
		$this->ticket = $ticket;
                $this->auction_id = $auction_id;
	}
        
        public function xml2array($contents, $get_attributes=1, $priority = 'tag') { 
            if(!$contents) return array(); 

            if(!function_exists('xml_parser_create')) { 
                //print "'xml_parser_create()' function not found!"; 
                return array(); 
            } 

            //Get the XML parser of PHP - PHP must have this module for the parser to work 
            $parser = xml_parser_create(''); 
            xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss 
            xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
            xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
            xml_parse_into_struct($parser, trim($contents), $xml_values); 
            xml_parser_free($parser); 

            if(!$xml_values) return;//Hmm... 

            //Initializations 
            $xml_array = array(); 
            $parents = array(); 
            $opened_tags = array(); 
            $arr = array(); 

            $current = &$xml_array; //Refference 

            //Go through the tags. 
            $repeated_tag_index = array();//Multiple tags with same name will be turned into an array 
            foreach($xml_values as $data) { 
                unset($attributes,$value);//Remove existing values, or there will be trouble 

                //This command will extract these variables into the foreach scope 
                // tag(string), type(string), level(int), attributes(array). 
                extract($data);//We could use the array by itself, but this cooler. 

                $result = array(); 
                $attributes_data = array(); 
         
                if(isset($value)) { 
                    if($priority == 'tag') $result = $value; 
                    else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode 
                } 

                //Set the attributes too. 
                if(isset($attributes) and $get_attributes) { 
                    foreach($attributes as $attr => $val) { 
                        if($priority == 'tag') $attributes_data[$attr] = $val; 
                        else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr' 
                    } 
                } 

                //See tag status and do the needed. 
                if($type == "open") {//The starting of the tag '<tag>' 
                    $parent[$level-1] = &$current; 
                    if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag 
                        $current[$tag] = $result; 
                        if($attributes_data) $current[$tag. '_attr'] = $attributes_data; 
                        $repeated_tag_index[$tag.'_'.$level] = 1; 
                            
                        $current = &$current[$tag]; 

                    } else { //There was another element with the same tag name 

                        if(isset($current[$tag][0])) {//If there is a 0th element it is already an array 
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
                            $repeated_tag_index[$tag.'_'.$level]++; 
                        } else {//This section will make the value an array if multiple tags with the same name appear together 
                            $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array 
                            $repeated_tag_index[$tag.'_'.$level] = 2; 
                     
                            if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well 
                                $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
                                unset($current[$tag.'_attr']); 
                            } 
                                
                        } 
                        $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1; 
                        $current = &$current[$tag][$last_item_index]; 
                    } 

                } elseif($type == "complete") { //Tags that ends in 1 line '<tag />' 
                    //See if the key is already taken. 
                    if(!isset($current[$tag])) { //New Key 
                        $current[$tag] = $result; 
                        $repeated_tag_index[$tag.'_'.$level] = 1; 
                        if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data; 
                            
                    } else { //If taken, put all things inside a list(array) 
                        if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array... 

                            // ...push the new element into that array. 
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
                     
                            if($priority == 'tag' and $get_attributes and $attributes_data) { 
                                $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
                            } 
                            $repeated_tag_index[$tag.'_'.$level]++; 

                        } else { //If it is not an array... 
                            $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value 
                            $repeated_tag_index[$tag.'_'.$level] = 1; 
                            if($priority == 'tag' and $get_attributes) { 
                                if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well 
                                        
                                    $current[$tag]['0_attr'] = $current[$tag.'_attr']; 
                                    unset($current[$tag.'_attr']); 
                                } 
                         
                                if($attributes_data) { 
                                    $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data; 
                                } 
                            } 
                            $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken 
                        } 
                    } 

                } elseif($type == 'close') { //End of tag '</tag>' 
                    $current = &$parent[$level-1]; 
                } 
            } 
     
            return($xml_array); 
        }          
        
        //결제정보수신
        public function getPayInfo($ticket,$payno){
            $requestXmlBody = '<?xml version="1.0" encoding="utf-8"?>';
            $requestXmlBody .= '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">';
            $requestXmlBody .= '<soap:Header>';
            $requestXmlBody .= '    <EncryptedTicket xmlns="http://www.auction.co.kr/Security">';
            $requestXmlBody .= '        <Value>'.$ticket.'</Value>';
            $requestXmlBody .= '    </EncryptedTicket>';
            $requestXmlBody .= '</soap:Header>';
            $requestXmlBody .= '<soap:Body>';
            $requestXmlBody .= '    <GetIpayAccountNumb xmlns="http://www.auction.co.kr/IpayService/Ipay">';
            $requestXmlBody .= '    <payNo>'.$payno.'</payNo>';
            $requestXmlBody .= '</GetIpayAccountNumb>';
            $requestXmlBody .= '</soap:Body>';
            $requestXmlBody .= '</soap:Envelope>';
          
            $requestXmlBody = str_replace("&", "&amp;", $requestXmlBody);


            // Load the XML Document to Print Request SOAP
            $requestDoc = new DomDocument();
            $requestDoc->loadXML($requestXmlBody);            
            
            //Create a new auction session with all details pulled in from included auctionSession.php
            $session = new ipaySession($this->serverUrl, "http://www.auction.co.kr/IpayService/Ipay/GetIpayAccountNumb");
	
            //send the request and get response
            $responseXml = $session->sendHttpRequest($requestXmlBody);
            
            Mage::log("requestXmlBody:".$requestXmlBody);
            Mage::log("responseXml :".$responseXml);
            
	
            // Process Response
            return $this->xml2array($responseXml);            
        
        }        
        
	
        //주문취소요청
        public function doCancel($itemid,$orderno){
            $requestXmlBody= '<?xml version="1.0" encoding="utf-8"?>'   ;
            $requestXmlBody.= '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">';
            $requestXmlBody.= '  <soap:Header>';
            $requestXmlBody.= '      <EncryptedTicket xmlns="http://www.auction.co.kr/Security">';
            $requestXmlBody.= '         <Value>'.$this->ticket.'</Value>';
            $requestXmlBody.= '          </EncryptedTicket>';
            $requestXmlBody.= '  </soap:Header>';
            $requestXmlBody.= '  <soap:Body>';
            $requestXmlBody.= '      <IpayDenySell xmlns="http://www.auction.co.kr/IpayService/Ipay">';
            $requestXmlBody.= '          <req SellerID="'.$this->auction_id.'" ItemID="'.$itemid.'" OrderNo="'.$orderno.'" DenySellReason="OtherReason" />';
            $requestXmlBody.= '      </IpayDenySell>';
            $requestXmlBody.= '  </soap:Body>';
            $requestXmlBody.= '  </soap:Envelope>';
            
            $requestXmlBody = str_replace("&", "&amp;", $requestXmlBody);
            // Load the XML Document to Print Request SOAP
            $requestDoc = new DomDocument();
            $requestDoc->loadXML($requestXmlBody);            
            
            //Create a new auction session with all details pulled in from included auctionSession.php
            $session = new ipaySession($this->serverUrl, $this->action);
	
            //send the request and get response
            $responseXml = $session->sendHttpRequest($requestXmlBody);
            Mage::log("requestXmlBodyCancel:".$requestXmlBody);
            Mage::log("responseXmlCancel :".$responseXml);
	
            // Process Response
            return $this->processResponse ($responseXml);            
        
        }
       
        //*** 발주확인하기*/
        public function doPlaceOrder($orderno){
            $requestXmlBody =   '<?xml version="1.0" encoding="utf-8"?>';
            $requestXmlBody .=  '<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">';
            $requestXmlBody .=  '<soap12:Header>';
            $requestXmlBody .=  '   <EncryptedTicket xmlns="http://www.auction.co.kr/Security">';
            $requestXmlBody .=  '       <Value>'.$this->ticket.'</Value>';
            $requestXmlBody .=  '   </EncryptedTicket>';
            $requestXmlBody .=  '</soap12:Header>';
            $requestXmlBody .=  '<soap12:Body>';
            $requestXmlBody .=  '<IpayConfirmReceivingOrder xmlns="http://www.auction.co.kr/IpayService/Ipay">';
            $requestXmlBody .=  '<req OrderNo="'.$orderno.'" />';
            $requestXmlBody .=  '</IpayConfirmReceivingOrder>';
            $requestXmlBody .=  '</soap12:Body>';
            $requestXmlBody .=  '</soap12:Envelope>';
            
            
            $requestXmlBody = str_replace("&", "&amp;", $requestXmlBody);
            

            // Load the XML Document to Print Request SOAP
            $requestDoc = new DomDocument();
            $requestDoc->loadXML($requestXmlBody);
            
            //Create a new auction session with all details pulled in from included auctionSession.php
            $session = new ipaySession($this->serverUrl, "http://www.auction.co.kr/IpayService/Ipay/IpayConfirmReceivingOrder");
	
            //send the request and get response
            $responseXml = $session->sendHttpRequest($requestXmlBody);
            
            Mage::log("requestXmlBodyInvoice:".$requestXmlBody);
            Mage::log("responseXmlInvoice :".$responseXml);
            
            // Process Response
            return $this->processResponse ($responseXml);             
        }
        
        //*** 배송하기*/
        public function doShipment($orderno,$invoiceno,$carriercode){
            $shipcompany = new Esellers_Ipay_Model_System_Config_Source_Company();
            $shiparr = $shipcompany->toArray();
            //$company_code = Mage::getStoreConfig("payment/ipay/shipcompany");
            
            $company_name = iconv("UTF-8","EUC-KR",$shiparr[$carriercode]);
            
            $requestXmlBody =   '<?xml version="1.0" encoding="utf-8"?>';
            $requestXmlBody .=  '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">';
            $requestXmlBody .=  '  <soap:Header>';
            $requestXmlBody .=  '<EncryptedTicket xmlns="http://www.auction.co.kr/Security">';
            $requestXmlBody .=  '<Value>'.$this->ticket.'</Value>';
            $requestXmlBody .=  '</EncryptedTicket>';
            $requestXmlBody .=  '</soap:Header>';
            $requestXmlBody .=  '<soap:Body>';
            $requestXmlBody .=  '<DoIpayShippingGeneral xmlns="http://www.auction.co.kr/IpayService/Ipay">';
            $requestXmlBody .=  '<req SellerID="'.$this->auction_id.'" OrderNo="'.$orderno.'">';
            $requestXmlBody .=  '<RemittanceMethod RemittanceMethodType="Emoney" RemittanceAccountName="" RemittanceAccountNumber="" RemittanceBankCode="" xmlns="http://schema.auction.co.kr/Arche.API.xsd" />';
            $requestXmlBody .=  '<ShippingMethod SendDate="'.date("Y-m-d").'" InvoiceNo="'.$invoiceno.'" MessageForBuyer="" ShippingMethodClassficationType="Door2Door" DeliveryAgency="'.$carriercode.'" DeliveryAgencyName="'.iconv("UTF-8", "EUC-KR",$company_name).'" ShippingEtcMethod="Nothing" ShippingEtcAgencyName="" xmlns="http://schema.auction.co.kr/Arche.API.xsd" />';
            $requestXmlBody .=  '</req>';
            $requestXmlBody .=  '</DoIpayShippingGeneral>';
            $requestXmlBody .=  '</soap:Body>';
            $requestXmlBody .=  '</soap:Envelope>';

            $requestXmlBody = str_replace("&", "&amp;", $requestXmlBody);
            

            // Load the XML Document to Print Request SOAP
            $requestDoc = new DomDocument();
            $requestDoc->loadXML($requestXmlBody);
            
            //Create a new auction session with all details pulled in from included auctionSession.php
            $session = new ipaySession($this->serverUrl, "http://www.auction.co.kr/IpayService/Ipay/DoIpayShippingGeneral");
	
            //send the request and get response
            $responseXml = $session->sendHttpRequest($requestXmlBody);
            
            Mage::log("requestXmlBodyShipment:".$requestXmlBody);
            Mage::log("responseXmlShipment :".$responseXml);
            
            // Process Response
            return $this->processResponse ($responseXml);
        }
        	
	/*** 서비스를 실행(호출)한다.*/ 
	public function doService($orderQuery){ 
  
	// Set Request SOAP Message
	$requestXmlBody =  '<?xml version="1.0" encoding="utf-8"?>	';
	$requestXmlBody .=  '<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"> ';
	$requestXmlBody .=  '  <soap:Header>		';
	$requestXmlBody .=  '   <EncryptedTicket xmlns="http://www.auction.co.kr/Security">		';
	$requestXmlBody .=	'   <Value>' . $this->ticket .  '</Value> ';
	$requestXmlBody .=  '		</EncryptedTicket> ';
	$requestXmlBody .=  '	</soap:Header> ';
	$requestXmlBody .=  '  <soap:Body>		';
	$requestXmlBody .=  '    <InsertIpayOrder xmlns="http://www.auction.co.kr/IpayService/Ipay">';
	$requestXmlBody .=  $orderQuery; 
	$requestXmlBody .=  '</InsertIpayOrder> ';
	$requestXmlBody .=  '</soap:Body> ';
	$requestXmlBody .=  '</soap:Envelope> '; 
	
			
	$requestXmlBody = str_replace("&", "&amp;", $requestXmlBody);


	// Load the XML Document to Print Request SOAP
	$requestDoc = new DomDocument();
	$requestDoc->loadXML($requestXmlBody);
        
 	//Create a new auction session with all details pulled in from included auctionSession.php
	$session = new ipaySession($this->serverUrl, $this->action);
	
	//send the request and get response
	$responseXml = $session->sendHttpRequest($requestXmlBody);
        
	// Process Response
	return $this->processResponse ($responseXml);
	}
	
	/**
	 * Request SOAP Message를 서버에 요청하고 받아온 Response SOAP Message를 가지고 처리한다.
	 * $responseXml	: Response SOAP Message
	 */
	private function processResponse($responseXml){
		
		if(stristr($responseXml, 'HTTP 404') || $responseXml == '') {
			die('<P>$responseXml Error sending request');
		} else {
			//Xml string is parsed and creates a DOM Document object
			$responseDoc = new DomDocument();
			$responseDoc->loadXML($responseXml);  

                        
			// Error 
			$eleFaultcode = $responseDoc->getElementsByTagName('faultcode')->item(0);			
			$eleFaultstring = $responseDoc->getElementsByTagName('faultstring')->item(0); 
			$eleResult =  $responseDoc->getElementsByTagName('InsertIpayOrderResult')->item(0); 
			
			if ((empty($eleFaultcode)) && (!empty($eleResult)))
			{   
				return $eleResult->firstChild->nodeValue; 
			}
			else{			 
				$this->processError($eleFaultcode, $eleFaultstring);
			}			
		} 
		return "";
	}
	
	
	/**
	 * 에러 처리를 한다.
	 * $eleFaultcode	: 오류 코드 메시지
	 * $eleFaultstring	: 오류 메시지
	 */
	private function processError($eleFaultcode, $eleFaultstring){
		if ($eleFaultcode != null) Mage::log( htmlentities ($eleFaultcode->nodeValue, ENT_NOQUOTES, "UTF-8"));
		if ($eleFaultstring != null) Mage::log("faultstring : ".iconv("UTF-8", "EUC-KR", urldecode (htmlentities ($eleFaultstring->nodeValue, ENT_NOQUOTES, "UTF-8"))));
	}
}
?>