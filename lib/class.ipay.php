<?

class auctionSession
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

class requestCartNo
{ 	  	 
	private $serverUrl = "https://api.auction.co.kr/ArcheSystem/IpayService.asmx";	//실제 운영 서버 주소
	private $action = "http://www.auction.co.kr/IpayService/Ipay/InsertIpayOrder";  
	private $ticket; 
	
	
	public function __construct($ticket){ 
		$this->ticket = $ticket;
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
	
	// Print Request SOAP 
	/*echo "<PRE>";
	echo "<STRONG>* REQUEST SOAP</STRONG><BR>";
	echo htmlentities ($requestDoc->saveXML());
	echo "</PRE>";*/
	 
	
 	//Create a new auction session with all details pulled in from included auctionSession.php
	$session = new auctionSession($this->serverUrl, $this->action);
	
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
			die('<P>Error sending request');
		} else {
			//Xml string is parsed and creates a DOM Document object
			$responseDoc = new DomDocument();
			$responseDoc->loadXML($responseXml);  
			
			
			
			// Print Response SOAP  
			/*echo "<PRE>";
			echo "<STRONG>* RESPONSE SOAP</STRONG><BR>";
			echo "<BR>".iconv("UTF-8", "EUC-KR", urldecode (htmlentities ($responseDoc->saveXML(), ENT_NOQUOTES, "UTF-8")) );
			echo "</PRE>";			*/
		  
			
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
		if ($eleFaultcode != null) echo "faultcode : ".iconv("UTF-8", "EUC-KR", urldecode (htmlentities ($eleFaultcode->nodeValue, ENT_NOQUOTES, "UTF-8")))."<BR>";
		if ($eleFaultstring != null) echo "faultstring : ".iconv("UTF-8", "EUC-KR", urldecode (htmlentities ($eleFaultstring->nodeValue, ENT_NOQUOTES, "UTF-8")))."<BR>";
	}
}

include_once dirname(__FILE__)."/nusoap.php";
include_once dirname(__FILE__)."/func.xml.php";
@include_once dirname(__FILE__)."/var.ipay.php";

class ipay {

	var $items = array(), $pay_price = 0;
	var $cfg, $mode, $data, $request, $response, $error, $errormsg;
	var $orderno, $payno, $cartno, $itemno, $sdate, $edate;

	function ipay(){
		include dirname(__FILE__)."/conf.php";
		$this->cfg = $cfg[ipay];
	}

	function reset(){
		unset($this->orderno); unset($this->payno); unset($this->cartno); unset($this->itemno); unset($this->sdate);
		unset($this->edate); unset($this->data); unset($this->error); unset($this->errormsg);
		unset($this->request); unset($this->response);
	}

	function mksoapheader(){
		global $client;
		$client = new soap_client('https://api.auction.co.kr/ArcheSystem/IpayService.asmx?WSDL', true);
		$err = $client->getError();
		$header = '<EncryptedTicket xmlns="http://www.auction.co.kr/Security"><Value>'.$this->cfg[ticket].'</Value></EncryptedTicket>';
		$client->setHeaders($header);
	}

	function setOrderQueryXml($data){
		$r_items = array();
		$goods_price = 0;
		foreach ($this->items as $item){
			$goods_price += $item[item_price] * $item[order_qty];
			$r_items[] = "<IpayServiceItems item_name='$item[item_name]' ipay_itemno='$item[ipay_itemno]' item_option_name='$item[item_option_name]' item_price='$item[item_price]' order_qty='$item[order_qty]' item_url='$item[item_url]' thumbnail_url='$item[thumbnail_url]' partner_code='ESELLERS' />";
			/*add_shipping_price_jeju='' add_shipping_price_etc=''*/
		}
		rsort($r_items);

		$ipay->pay_price = $goods_price + $data[shipping_price];

		/*
		$shipping_type 배송타입 1 - 무료 , 2 - 착불 , 3 - 선배송료결제 
		(1,2)일때 는 Shipping_price가 0이여 함
		*/

		if ($data[shipping_price]) $data[shipping_type] = 3;
		
		if (!$data[back_url]) $data[back_url] = $_SERVER[HTTP_REFERER];
		if (!$data[service_url]) $data[service_url] = "http://$_SERVER[HTTP_HOST]/ipay/receiveXml.php";
		if (!$data[redirect_url]) $data[redirect_url] = "http://$_SERVER[HTTP_HOST]/ipay/return.php";
		if (!$data[move_to_redirect_url]) $data[move_to_redirect_url] = "true";

		$xml = "<ORDER payment_rule='0' pay_price='$ipay->pay_price' shipping_price='$data[shipping_price]' shipping_type='$data[shipping_type]' back_url='$data[back_url]' service_url='$data[service_url]' redirect_url='$data[redirect_url]' is_address_required='false' buyer_name='$data[buyer_name]' buyer_tel_no='$data[buyer_tel_no]' buyer_email='$data[buyer_email]' move_to_redirect_url='$data[move_to_redirect_url]' />
		<ITEMS>".implode("",$r_items)."</ITEMS>";
		return $xml;
	}

	function setparam(){
		switch ($this->mode){
			case "GetIpayPaidOrderList":
				$params = array(
					'req'	=> array(
						'SearchType' => 'OrderNo',
						'SearchValue' => $this->orderno,
						)
				);
				break;
			case "GetIpayAccountNumb":
				$params = array('payNo'	=> $this->payno);
				break;
			case "IpayConfirmReceivingOrder":
				$params = array(
					'req'	=> array(
						'OrderNo' => $this->orderno,
						)
				);
				break;
			case "GetIpayOrderConfirm":
				$params = array('ipayCartNo' => $this->cartno);
				break;
			case "GetIpayReturnList":
				$params = array(
					'req'	=> array(
						'SearchFlags'	=> 'All',
						'SearchType'	=> 'None',
						'SearchDateType'=> 'None',
						'PageSize'		=> 100,
						)
				);
				break;
			case "GetIpayExchangeRequestList":
				$params = array(
					'req'	=> array(
						'PageNumber'	=> '1',
						)
				);
				break;
			case "":
				$params = array(
					'ipayCartNo'	=> $this->cartno,
					'ipayItemNo'	=> $this->itemno,
					);
				break;
		}
		return array($params);
	}

	function exec($mode,$params=''){

		global $client;
		$this->mode = $mode;
	
		$this->mksoapheader();
		if (!$params) $params = $this->setparam();

		//debug($params);

		$result = $client->call($this->mode, $params);
		//debug($client->request);
		//debug($client->response);

		$this->request = $client->request;
		$this->response = $client->response;

		preg_match("/<\?xml(.*?)<\/soap:Envelope>/s",$client->response,$match);
		$ret = xml2array($match[0]);
		
		//debug($ret);
		switch ($this->mode){
			case "GetIpayPaidOrderList":
				$data = $ret["soap:Envelope"]["soap:Body"]["GetIpayPaidOrderListResponse"]["GetIpayPaidOrderListResult"]["GetOrderListResponseT_attr"];
				if ($data[DistPostNo]) $data[DistPostNo] = substr($data[DistPostNo],0,3)."-".substr($data[DistPostNo],3);
				break;
			case "GetIpayReturnList":
				$loop = $ret["soap:Envelope"]["soap:Body"][$this->mode."Response"][$this->mode."Result"]["ReturnList"];
				foreach ($loop as $k=>$v){
					if (strpos($k,"_attr")!==false){
						$key = str_replace("_attr","",$k);
						$loop[$key] = array_merge($loop[$key],$v);
						unset($loop[$k]);
					}
				}
				$data = $loop;
				
				//debug($data);
				break;
			case "GetIpayExchangeRequestList":
				$loop = $ret["soap:Envelope"]["soap:Body"][$this->mode."Response"][$this->mode."Result"];
				$data = $loop;
				break;
			/*
			case "GetIpayOrderConfirm":
				$data = $ret["soap:Envelope"]["soap:Body"][$this->mode."Response"][$this->mode."Result"];
				break;
			*/
			default:
				$data = $ret["soap:Envelope"]["soap:Body"][$this->mode."Response"][$this->mode."Result_attr"];
				break;
		}
		$err = $ret["soap:Envelope"]["soap:Body"]["soap:Fault"];

		if ($this->mode=="IpayDenySell" && $data[DenySellResponseType]=="Fail") $err[faultstring] = "error";
		if ($err){
			$this->data = array();
			$this->error = 1;
			$this->errormsg = $err[faultstring];
		} else {
			$this->data = $data;
			$this->error = 0;
		}
	}

	function getOrderInfo($payno){
		$this->reset();
		$this->payno = $payno;
		$this->exec("GetIpayAccountNumb");
		return $this->data;
	}

	function getOrderItemInfo($orderno){
		$this->reset();
		$this->orderno = $orderno;
		$this->exec("GetIpayPaidOrderList");
		return $this->data;
	}

	function getReturnList(){
		$this->reset();
		$this->exec("GetIpayReturnList");
		return $this->data;
	}

	function getExchangeList(){
		$this->reset();
		$this->exec("GetIpayExchangeRequestList");
		return $this->data;
	}

	function getOrderData($payno){
		$r_orderno = array();
		$data = $this->getOrderInfo($payno);
		$r_orderno = explode("@",$data[AuctionOrderNos]);
		$r_orderno = array_notnull($r_orderno);
		$data[items] = array();
		foreach ($r_orderno as $orderno){
			$data[items][] = $this->getOrderItemInfo($orderno);
		}
		return $data;
	}

	function confirmOrder($orderno){
		$this->reset();
		$this->orderno = $orderno;
		$this->exec("IpayConfirmReceivingOrder");
		//return $this->data;
		return 1-$this->error;
	}

	function shippingOrder($orderno,$shippingcomp,$shippingcode){
		global $r_shippingcomp;
		$this->reset();
		$params = array(
			'req'	=> array(
				'SellerID'	=> $this->cfg[sellerid],
				'OrderNo'	=> $orderno,
				'RemittanceMethod'	=> array(
					'RemittanceMethodType' => 'Emoney',
					'RemittanceAccountName' => '',
					'RemittanceAccountNumber' => '',
					'RemittanceBankCode' => '',
					),
				'ShippingMethod'	=> array(
					'SendDate'	=> date("Y-m-d"),
					'InvoiceNo'	=> $shippingcode,
					'MessageForBuyer' => '',
					'ShippingMethodClassficationType' => 'Door2Door',
					'DeliveryAgency' => $shippingcomp,
					'DeliveryAgencyName' => $r_shippingcomp[$shippingcomp],
					'ShippingEtcMethod' => 'Nothing',
					'ShippingEtcAgencyName' => '',
					),
				),
			);
		$this->exec("DoIpayShippingGeneral",array($params));
		return 1-$this->error;
	}

	function cancelOrder($orderno,$itemno){
		$this->reset();
		$params = array(
			'req'	=> array(
				'SellerID'	=> $this->cfg[sellerid],
				'ItemID'	=> $itemno,
				'OrderNo'	=> $orderno,
				'DenySellReason' => 'RunOutOfStock',
				),
			);
		$this->exec("IpayDenySell",array($params));
		return 1-$this->error;
	}

	/*

	function isConfirmedOrder($cartno){
		$this->reset();
		$this->cartno = $cartno;
		$this->exec("GetIpayOrderConfirm");
		return $this->data;
	}

	### 옥션주문정보추출
	function GetIpayPaidOrderList($auctionordno){
		
		global $client;
		$this->mksoapheader();

		$params = array(
			'req'	=> array(
				'SearchType' => 'OrderNo',
				'SearchValue' => $auctionordno,
				)
		);
		$params = array($params);

		$result = $client->call('GetIpayPaidOrderList', $params);
		//debug(str_replace(">",">\r\n",$client->request));
		//debug(str_replace(">",">\r\n",$client->response));

		preg_match("/<\?xml(.*?)<\/soap:Envelope>/s",$client->response,$match);
		$ret = xml2array($match[0]);
		
		$data = $ret["soap:Envelope"]["soap:Body"]["GetIpayPaidOrderListResponse"]["GetIpayPaidOrderListResult"]["GetOrderListResponseT_attr"];
		$data[DistPostNo] = substr($data[DistPostNo],0,3)."-".substr($data[DistPostNo],3);
		return $data;
	}

	function GetIpayAccountNumb($payno){
		global $client;
		$this->mksoapheader();

		$params = array(
			'payNo'	=> $payno,
		);
		$params = array($params);

		$result = $client->call('GetIpayAccountNumb', $params);
		//debug(str_replace(">",">\r\n",$client->request));
		//debug(str_replace(">",">\r\n",$client->response));

		preg_match("/<\?xml(.*?)<\/soap:Envelope>/s",$client->response,$match);
		$ret = xml2array($match[0]);

		$data = $ret["soap:Envelope"]["soap:Body"]["GetIpayAccountNumbResponse"]["GetIpayAccountNumbResult_attr"];
		return $data;
	}

	*/

}

### 배열 null 제거 함수
if (!function_exists('array_notnull')) {
	function array_notnull($arr){
		if (!is_array($arr)) return;
		foreach ($arr as $k=>$v) if (!$v) unset($arr[$k]);
		return $arr;
	}
}

### 배열/클래스 출력 함수
if (!function_exists('debug')) {
	function debug($data){
		print "<div style='background:#000000;color:#00ff00;padding:10px;text-align:left'><xmp style=\"font:8pt 'Courier New'\">";
		print_r($data);
		print "</xmp></div>";
	}
}

?>