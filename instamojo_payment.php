<?php 
// mapyment gate way link 
function get_base_url(){
    $base_url="";
    //get request scheme type
    if(!empty($_SERVER['HTTPS'])){
        $base_url = 'https://';
    }
    else{
        $base_url = 'http://';
    }
    $base_url .=$_SERVER['HTTP_HOST']; // add host name
    //find if any sub directory present
    $base_url .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
    return $base_url;
}

function get_payment_link($amount=0, $purpose="",$buyer_name="",$phone="",$email=""){
    $payment_request_url="";
    if($amount>0 && !empty($purpose)){
        $root = get_base_url();
        $baseurl= $root."backend_v1.php";
        $redirect_url="$baseurl?func=payment_gateway_return";
		$webhook_url="$baseurl?func=payment_gateway_webhook";
        $webhook_url=""; //need live url
        //validate the phoen number is valid one 
        if(preg_match('/[0-9]/',$phone)){
            if(strlen($phone)==10){
                $phone="+91".$phone;
            }
            else{
                $phone="";
            }
        }
        else{
            $phone="";
        }
        
        //validate the email 
        if(!filter_var($email,FILTER_VALIDATE_EMAIL)){
            $email="";
        }
        //gate way config
		$payload = Array(
			'purpose' => $purpose,
			'amount' => $amount,
			'buyer_name' => $buyer_name,
			'phone' => $phone,
			'email' => $email,
			'send_sms' => false,
			'send_email' => false,
			'redirect_url' => $redirect_url,
			'webhook' => $webhook_url,
            'allow_repeated_payments' => false,
            'shipping_address'=>'shipping_address custome',
        );
        $endPoint="payment-requests/";
		$mode="post";
        $response = call_curl($endPoint,$payload,$mode);
        
        // track the request for payment url generating
        if(isset($response['success']) && $response['success']){
			$payment_request = $response['payment_request'];
			if(isset($payment_request['status']) && $payment_request['status']=="Pending"){
				//now call the url to get the payment
				$payment_request_url = $payment_request['longurl'];
			}
        }
        else{
            $errors="";
            if(!empty($response['message'])){
                foreach($response['message'] as $message){
                    if(!empty($message)){
                        if(is_array($message) ){
                            $errors.=$message[0]."<>";
                        }
                        else{
                            $errors.=$message."<>";
                        }
                    }
                }
            }
            $payment_request_url = $baseurl."?func=payment_request_faild&errors=$errors";
        }
    }
    return $payment_request_url;
}

//payment gateway return  brouser 
function payment_request_faild(){
    echo $_GET['errors'];
}

function payment_gateway_return($data){
    
	$payment_status = $_GET['payment_status'];
	$payment_id = $_GET['payment_id'];
	$payment_request_id = $_GET['payment_request_id'];
	$responseDatas=array(
		'payment_status'=>$payment_status,
		'payment_id'=>$payment_id,
		'payment_request_id'=>$payment_request_id,
    );
    
	// now checke the status of the payment request
	$endPoint="payment-requests/$payment_request_id";
    $response = call_curl($endPoint,$payload=array(),$mode="get");
	
    // gel all teatails 
    $subject="";
    $message="";
    if(isset($response['success']) && $response['success']==1){//success
        $payment_request = $response['payment_request'];
        $amount = $payment_request['amount'];
        $buyer_name = $payment_request['buyer_name'];
        $purpose = $payment_request['purpose'];
        $status = $payment_request['status'];
        $payments = $payment_request['payments'];
        $buyer_phone = isset($payments[0]['buyer_phone'])?$payments[0]['buyer_phone']:"";
        $buyer_email = isset($payments[0]['buyer_email'])?$payments[0]['buyer_email']:"";
        $fees = isset($payments[0]['fees'])?$payments[0]['fees']:"";
        $variants = isset($payments[0]['variants'])?$payments[0]['variants']:"";
        $affiliate_commission = isset($payments[0]['affiliate_commission'])?$payments[0]['affiliate_commission']:"";
        $instrument_type = isset($payments[0]['instrument_type'])?$payments[0]['instrument_type']:"";
        $billing_instrument = isset($payments[0]['billing_instrument'])?$payments[0]['billing_instrument']:"";
        $created_at = isset($payments[0]['created_at'])?$payments[0]['created_at']:"";
        if(!empty($payments)){
            $payment=$payments[0];
        }
        //
        $subject="Payment received of amount $amount for $purpose";
        $message = "Hi,\nPayment details are as follows\n";
        $message .="\nPayment Status : ".$payment_status;
        $message .="\nPayment ID : ".$payment_id;
        $message .="\nPayment Request ID : ".$payment_request_id;
        $message .="\nBuyer Name : ".$buyer_name;
        $message .="\nBuyer Phone : ".$buyer_phone;
        $message .="\nBuyer Email : ".$buyer_email;
        $message .="\nFees : ".$fees;
        $message .="\nInstrument Type : ".$instrument_type;
        $message .="\nBilling Instrument : ".$billing_instrument;
        $message .="\nBilling Date Time : ".$created_at;
    }
    else{ // faild

    }
    //mail function of the server default called for send the mail with sunject 
    send_mail($subject,$message);
    //now redirect to success page
    $params = "?payment_status=$payment_status&payment_id=$payment_id&payment_request_id=$payment_request_id";
    $payment_return_url = get_base_url()."payment_response.html";
    $payment_return_url .=$params;
    header("Location:$payment_return_url");
}

// inter server call
function payment_gateway_webhook(){
    
}

function call_curl($endpath="",$payload=array(),$mode="post"){
	$paymentLink="https://www.instamojo.com/api/1.1/";
    $insta_api_key="d66166a28b21b167e7221a273deb8fcf";
    $insta_auth_token="926792f82702cbdcd68f15ac339b1472";
    $insta_salt="01103adc1aba4e948b0e3acf950d06f6";
    
	$header = array(
	"X-Api-Key:$insta_api_key",
	"X-Auth-Token:$insta_auth_token");
	$url = $paymentLink.$endpath;
	$callsedData=array(
		'url'=>$url,
		'header'=>$header,
		'payload'=>$payload,
	);
	
	//make payment request 
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
	curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
	//set request post data 
	if(strtolower($mode)=="post"){
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
	}
	$response = curl_exec($ch);
    curl_close($ch);
	return json_decode($response,true);
}

?>