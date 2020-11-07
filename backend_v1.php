<?php
global $response_status;
global $response_message;
include('instamojo_payment.php');

//assign values
$dbug=false;
$GLOBALS['response_status']=0;
$GLOBALS['response_message']='';
try{
    $functionName = (isset($_GET['func']))?trim($_GET['func']):'';
    if(!empty($functionName)){
        if(function_exists($functionName)){
            //$inputdata = file_get_contents('php://input');
            //$data = json_decode($inputdata, true);
            $data = $_POST;
            if($dbug){
                echo "<pre>";
                print_r($data);
            }
            $functionName($data);
        }
        else{
            $GLOBALS['response_message']="Request not found.";
            return_response();
        }
    }
    else{
        $GLOBALS['response_message']="invalid request";
        return_response();
    }
}catch(Exceptions $e){
    print_r($e);
    echo $e->getMessage();
    $GLOBALS['response_message']=$e->getMessage();
    return_response();
}

function return_response($extradata=array()){
    $returndata=array(
        'status'=>$GLOBALS['response_status'],
        'message'=>$GLOBALS['response_message'],
    );
    if(is_array($extradata) && !empty($extradata)){
        $returndata  = array_merge($returndata,$extradata);
    }
    die(json_encode($returndata));
}

//email section 
function send_mail($subject="", $message=""){
    if(empty($subject)){
        $subject="Subject not set";
    }
    if(empty($message)){
        $message="Not set";
    }
    $headers = "Bcc: saninfowb@gmail.com";
    $adminReceiver = "legalution.in@gmail.com";
    mail($adminReceiver,$subject,$message,$headers);
}

//payment gayway function 
function transaction_key($key_name=""){
    $key = "$key_name".date("Y/m")."/".rand(9999,1000000);
    return $key;
}

//calling function 

// TRADEMARK SECTION
function trademark($request){
    $trademark_option = isset($request['trademark_option'])?$request['trademark_option']:'';
    $name = isset($request['name'])?$request['name']:'';
    $email = isset($request['email'])?$request['email']:'';
    $phone = isset($request['phone'])?$request['phone']:'';
    //set trade mark prices 
    $prices = trademark_plan_prices(1);//array formation price
    $response_data=array();
    if(!empty($trademark_option)){
        //checked price section 
        $price = isset($prices[strtolower($trademark_option)])?$prices[strtolower($trademark_option)]:0;
        if($price>0){
            $transaction_id = transaction_key('TRD-');
            //now send a mail to admin about the user
            $subject="Customer looking for trademark.";
            $message = "Hi,\nCustomers details are as follows\n";
            $message .="\nCustomer Name : ".$name;
            $message .="\nCustomer Phone : ".$phone;
            $message .="\nCustomer Email : ".$email;
            $message = "\nCustomer looking for \n";
            $message = "\nTrademark : ".$trademark_option;
            $message = "\nPrice : Rs. ".number_format($price,2);
            $message = "\nTransaction ID : ".$transaction_id;
            //now send the mail 
            send_mail($subject,$message);

            //now need to open the payment url
            $purpose = "Trademark $trademark_option - $transaction_id";
            $payment_url = get_payment_link($price,$purpose,$name,$phone,$email);
            $response_data['payment_url']=$payment_url;
            $GLOBALS['response_status']=1;
        }
        else{
            $GLOBALS['response_message']="Trademark option price not found";
        }
    }
    else{
        $GLOBALS['response_message']="Invalid trademark option";
    }
    return_response($response_data);
}

function trademark_plan($request){
    $plan_id = isset($request["plan_id"])?$request['plan_id']:'';
    $name = isset($request['name'])?$request['name']:'';
    $email = isset($request['email'])?$request['email']:'';
    $phone = isset($request['phone'])?$request['phone']:'';
    $address = isset($request['address'])?$request['address']:'';
    $state = isset($request['state'])?$request['state']:'';
    $pin_code = isset($request['pin_code'])?$request['pin_code']:'';
    $company_name = isset($request['company_name'])?$request['company_name']:'';
    $gst_no = isset($request['gst_no'])?$request['gst_no']:'';
    $response_data=array();
    if(!empty($plan_id)){
        //checked price section 
        $plan = trademark_plan_prices(2,$plan_id);
        $plan_name = isset($plan['name'])?$plan['name']:'';
        $price = isset($plan['price'])?$plan['price']:0;
        if($price>0 && !empty($plan_name)){
            $transaction_id = transaction_key('TRD-');
            //now send a mail to admin about the user
            $subject="Customer looking for trademark.";
            $message = "Hi,\nCustomers details are as follows\n";
            $message .="\nCustomer Name : ".$name;
            $message .="\nCustomer Phone : ".$phone;
            $message .="\nCustomer Email : ".$email;
            $message .="\Plan Name : ".$plan_name;
            $message .="\Plan Price : ".$price;
            $message = "\nCustomer adress as follows\n";
            $message = "\nAddress :".$address;
            $message = "\nState :".$state;
            $message = "\nPIN :".$pin_code;
            $message = "\nCustomer company details as follows\n";
            $message = "\nCompany Name :".$company_name;
            $message = "\nGST No. :".$gst_no;

            $message = "\nTransaction ID : ".$transaction_id;
            //now send the mail 
            send_mail($subject,$message);

            //now need to open the payment url
            $purpose = "Trademark $plan_name - $transaction_id";
            $payment_url = get_payment_link($price,$purpose,$name,$phone,$email);
            $response_data['payment_url']=$payment_url;
            $GLOBALS['response_status']=1;
        }
        else{
            $GLOBALS['response_message']="Trademark plan price not found";
        }
    }
    else{
        $GLOBALS['response_message']="Invalid plan";
    }
    return_response($response_data);
}

function trademark_plan_prices($priceType=1,$plan_no=0){
    if($priceType==2){
        $plan_prices = [
            '1'=>array(
                'name'=>'Registration',
                'price'=>'5558'
            ),
            '2'=>array(
                'name'=>'Renewal',
                'price'=>'5557'
            ),
            '3'=>array(
                'name'=>'Objection',
                'price'=>'5559'
            ),
            '4'=>array(
                'name'=>'Opppsition',
                'price'=>'5556'
            ),
        ];
        if($plan_no>0){
            return (isset($plan_prices[$plan_no]))?$plan_prices[$plan_no]:array();
        }
    }
    else{
        $plan_prices = array(
            'registration'=>'5558',
            'renewal'=>'5557',
            'objection'=>'5559',
            'opppsition'=>'5556',
        );
        if(!empty($plan_no)){
            $plan_no = strtolower($plan_no);
            return (isset($plan_prices[$plan_no]))?$plan_prices[$plan_no]:'';
        }
    }
    
    return $plan_prices;
}

function get_trademark_price($request){
    $response_data=[];
    $plan_id = isset($_GET['plan'])?$_GET['plan']:0;
    if($plan_id>0){
        $plan = trademark_plan_prices(2,$plan_id);
        if(isset($plan['name'])){
            $price = isset($plan['price'])?$plan['price']:0;
            $plan_price=$plan['name']." (Rs. $price)";
            $response_data['plan_price'] = $plan_price;
            $response_data['plan_id'] = $plan_id;
            $GLOBALS['response_status']=1;
        }
        else{
            $GLOBALS['response_message']="Invalied plan.";
        }
    }
    else{
        $GLOBALS['response_message']="Invalied acceess.";
    }
    return_response($response_data);
}

// FSSAI SECTION
function fssai($request){
    $fssai_option = isset($request['fssai_option'])?$request['fssai_option']:'';
    $name = isset($request['name'])?$request['name']:'';
    $email = isset($request['email'])?$request['email']:'';
    $phone = isset($request['phone'])?$request['phone']:'';
    //company details
    $activity_type = isset($request['activity_type'])?$request['activity_type']:'';
    $company_name = isset($request['company_name'])?$request['company_name']:'';
    $company_trunover = isset($request['company_trunover'])?$request['company_trunover']:'';
    $product_name = isset($request['product_name'])?$request['product_name']:'';
    $company_address = isset($request['company_address'])?$request['company_address']:'';
    $state = isset($request['state'])?$request['state']:'';
    $pin_code = isset($request['pin_code'])?$request['pin_code']:'';
    //set trade mark prices 
    $prices = array(
        'basic lincense'=>'5556',
        'state lincense'=>'5557',
        'central lincense'=>'5558',
    );
    $response_data=array();
    if(!empty($fssai_option)){
        //checked price section 
        $price = isset($prices[strtolower($fssai_option)])?$prices[strtolower($fssai_option)]:0;
        if($price>0){
            $transaction_id = transaction_key('FSSAI-');
            //now send a mail to admin about the user
            $subject="Customer looking for FSSAI.";
            $message = "Hi,\nCustomers details are as follows\n";
            $message .="\nCustomer Name : ".$name;
            $message .="\nCustomer Phone : ".$phone;
            $message .="\nCustomer Email : ".$email;
            $message .="\FSSAI For : ".$fssai_option;
            $message = "\nCustomer Company details as follows\n";
            $message = "\nCompany Name : ".$company_name;
            $message = "\nCompany Trunover : ".$company_trunover;
            $message = "\nProduct Name : ".$product_name;
            $message = "\nAddress :".$company_address;
            $message = "\nState :".$state;
            $message = "\nPIN :".$pin_code;
            $message = "\nType Of Activity :".$activity_type;

            $message = "\nTransaction ID : ".$transaction_id;
            //now send the mail 
            send_mail($subject,$message);

            //now need to open the payment url
            $purpose = "FSSAI $fssai_option - $transaction_id";
            $payment_url = get_payment_link($price,$purpose,$name,$phone,$email);
            $response_data['payment_url']=$payment_url;
            $GLOBALS['response_status']=1;
        }
        else{
            $GLOBALS['response_message']="FSSAI option price not found";
        }
    }
    else{
        $GLOBALS['response_message']="Invalid fssai option";
    }
    return_response($response_data);
}

// fee advices 
function free_advice($request){
    $advice_for = isset($request["advise_for"])?$request['advise_for']:'';
    $name = isset($request['name'])?$request['name']:'';
    $email = isset($request['email'])?$request['email']:'';
    $phone = isset($request['phone'])?$request['phone']:'';
    
    //now send a mail to admin about the user
    $subject="Customer looking for advisore consultation";
    $message = "Hi,\nCustomers details are as follows\n";
    $message .="\nCustomer Name : ".$name;
    $message .="\nCustomer Phone : ".$phone;
    $message .="\nCustomer Email : ".$email;
    if(!empty($advice_for)){
        $message = "\n consultation on ".ucwords($advice_for);
    }
    //now send the mail 
    send_mail($subject,$message);
    $GLOBALS['response_status']=1;
    $GLOBALS['response_message']="Thank you for contacting us. We get back to you very soon.";
    return_response();
}

// APEDA SECTION 

function apeda_plan($request){
    $plan_id = isset($request["plan_id"])?$request['plan_id']:'';
    $name = isset($request['name'])?$request['name']:'';
    $email = isset($request['email'])?$request['email']:'';
    $phone = isset($request['phone'])?$request['phone']:'';
    $address = isset($request['address'])?$request['address']:'';
    $state = isset($request['state'])?$request['state']:'';
    $pin_code = isset($request['pin_code'])?$request['pin_code']:'';
    $response_data=array();
    if(!empty($plan_id)){
        //checked price section 
        $plan = apeda_plan_prices($plan_id);
        $plan_name = isset($plan['name'])?$plan['name']:'';
        $price = isset($plan['price'])?$plan['price']:0;
        if($price>0 && !empty($plan_name)){
            $transaction_id = transaction_key('apeda-');
            //now send a mail to admin about the user
            $subject="Customer looking for APEDA.";
            $message = "Hi,\nCustomers details are as follows\n";
            $message .="\nCustomer Name : ".$name;
            $message .="\nCustomer Phone : ".$phone;
            $message .="\nCustomer Email : ".$email;
            $message .="\Plan Name : ".$plan_name;
            $message .="\Plan Price : ".$price;
            /*
            $message = "\nCustomer adress as follows\n";
            $message = "\nAddress :".$address;
            $message = "\nState :".$state;
            $message = "\nPIN :".$pin_code;
            */
            $message = "\nTransaction ID : ".$transaction_id;
            //now send the mail 
            send_mail($subject,$message);

            //now need to open the payment url
            $purpose = "APEDA $plan_name - $transaction_id";
            $payment_url = get_payment_link($price,$purpose,$name,$phone,$email);
            $response_data['payment_url']=$payment_url;
            $GLOBALS['response_status']=1;
        }
        else{
            $GLOBALS['response_message']="APEDA plan price not found";
        }
    }
    else{
        $GLOBALS['response_message']="Invalid plan";
    }
    return_response($response_data);
}

function get_apeda_price($request){
    $response_data=[];
    $plan_id = isset($_GET['plan'])?$_GET['plan']:0;
    if($plan_id>0){
        $plan = apeda_plan_prices($plan_id);
        if(isset($plan['name'])){
            $price = isset($plan['price'])?$plan['price']:0;
            $plan_price=$plan['name']." (Rs. $price)";
            $response_data['plan_price'] = $plan_price;
            $response_data['plan_id'] = $plan_id;
            $GLOBALS['response_status']=1;
        }
        else{
            $GLOBALS['response_message']="Invalied plan.";
        }
    }
    else{
        $GLOBALS['response_message']="Invalied acceess.";
    }
    return_response($response_data);
}

function apeda_plan_prices($plan_no=0){
    $apeda_plan_prices = [
        '1'=>array(
            'name'=>'Basic',
            'price'=>'7699'
        ),
        '2'=>array(
            'name'=>'Premium',
            'price'=>'17900'
        ),
        '3'=>array(
            'name'=>'Standard',
            'price'=>'8400'
        ),
    ];
    if($plan_no>0){
        return (isset($apeda_plan_prices[$plan_no]))?$apeda_plan_prices[$plan_no]:array();
    }
    return $apeda_plan_prices;
}

//IEC SECTION
function iec($request){
    $name = isset($request['name'])?$request['name']:'';
    $email = isset($request['email'])?$request['email']:'';
    $phone = isset($request['phone'])?$request['phone']:'';
    //company details
    $company_name = isset($request['company_name'])?$request['company_name']:'';
    $company_type = isset($request['company_type'])?$request['company_type']:'';
    $pan_no = isset($request['pan_no'])?$request['pan_no']:'';
    $company_address = isset($request['company_address'])?$request['company_address']:'';
    $state = isset($request['state'])?$request['state']:'';
    $pin_code = isset($request['pin_code'])?$request['pin_code']:'';
    
    $response_data=array();
    $price = 1499;
    if($price>0){
        $transaction_id = transaction_key('IEC-');
        //now send a mail to admin about the user
        $subject="Customer looking for IEC.";
        $message = "Hi,\nCustomers details are as follows\n";
        $message .="\nCustomer Name : ".$name;
        $message .="\nCustomer Phone : ".$phone;
        $message .="\nCustomer Email : ".$email;
        $message = "\nCustomer Company details as follows\n";
        $message = "\nCompany Name : ".$company_name;
        $message = "\nCompany Type : ".$company_type;
        $message = "\nPAN : ".$pan_no;
        $message = "\nAddress :".$company_address;
        $message = "\nState :".$state;
        $message = "\nPIN :".$pin_code;

        $message = "\nTransaction ID : ".$transaction_id;
        //now send the mail 
        send_mail($subject,$message);

        //now need to open the payment url
        $purpose = "IEC Registration - $transaction_id";
        $payment_url = get_payment_link($price,$purpose,$name,$phone,$email);
        $response_data['payment_url']=$payment_url;
        $GLOBALS['response_status']=1;
    }
    else{
        $GLOBALS['response_message']="IEC price not found";
    }
    return_response($response_data);
}

//ISO SECTION 
function iso($request){
    $name = isset($request['name'])?$request['name']:'';
    $email = isset($request['email'])?$request['email']:'';
    $phone = isset($request['phone'])?$request['phone']:'';
    $certificate_type = isset($request['certificate_type'])?$request['certificate_type']:'';
    //now send a mail to admin about the user
    $subject="Customer looking for ISO certificate.";
    $message = "Hi,\nCustomers details are as follows\n";
    $message .="\nCustomer Name : ".$name;
    $message .="\nCustomer Phone : ".$phone;
    $message .="\nCustomer Email : ".$email;
    $message = "\Certificate Type : ".$certificate_type;
    //now send the mail 
    send_mail($subject,$message);
    $GLOBALS['response_status']=1;
    $GLOBALS['response_message']="Thank you for contacting us. We get back to you very soon.";
    return_response();
}
?>