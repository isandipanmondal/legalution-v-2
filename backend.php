<?php
define('recieverEmail',"legalution.in@gmail.com");
define('printLog',false);
//get mail headers 
function mail_headers(){
    $headers = "Bcc: saninfowb@gmail.com";
    return $headers;
}

//get all the function 
function detail_enquery($data){
    //send the mail to the author/owner about the request 
    $subject = "You have a new inquery request from customer";
    $message = "Hello Admin,\nContact Person details is as follows:\n Name : ".$data['name'];
    $message .="\n Email : ".$data['email'];
    $message .="\n Phone : ".$data['phone'];
    $message .="\n Service : ".$data['service'];
    //mail function of the server default called for send the mail with sunject 
    $headers = mail_headers();
    mail(recieverEmail,$subject,$message,$headers);
    $msg='Thank you, Your request send to our authorized person, we will contact you very soon.';
    return_response($status=1,$msg,$data=array());
}

// call back request 
function callback_request($data){
    //send the mail to the author/owner about the request 
    $subject = "You have a new callback request from customer";
    $message = "Hello Admin,\nContact Person details is as follows:\n Name : ".$data['name'];
    $message .="\nPhone : ".$data['phone'];
    //mail function of the server default called for send the mail with sunject 
    $headers = mail_headers();
    mail(recieverEmail,$subject,$message,$headers);
    $msg='Thank you, Your request send to our authorized person, we will contact you very soon.';
    return_response($status=1,$msg,$data=array());
}

// customer complaine basic details 
function complaine_basic_info($data){
    common_print($data);
    //get all the param value
    $name = isset($data['name'])?$data['name']:'';
    $mobile_no = isset($data['mno'])?$data['mno']:'';
    $email = isset($data['Email'])?$data['Email']:'';
    $address = isset($data['Address'])?$data['Address']:'';
    $pincode = isset($data['pincode'])?$data['pincode']:'';
    $state = isset($data['state'])?$data['state']:'';
    $cata = isset($data['cata'])?$data['cata']:'';

    $subject="Customer looking for fill up a complaine.";
    $message = "Hi,\nCustomers details are as follows\n";
    $message .="\nCustomer Name : ".$name;
    $message .="\nCustomer Phone : ".$mobile_no;
    $message .="\nCustomer Email : ".$email;
    $message .="\nCustomer Address : ".$address;
    $message .="\nCustomer PIN : ".$pincode;
    $message .="\nState : ".$state;
    $message .="\nComplain Category : ".$cata;
    //
    $headers = mail_headers();
    mail(recieverEmail,$subject,$message,$headers);
    $msg='We connect you very soon.';
    return_response($status=1,$msg,array());
}

// complained full details and plain
function customer_complain(){
    // receive all the get param
    $get_params = $_GET;
    $post_params = $_POST;
    $file_params = $_FILES;
    //get all the param value
    $name = isset($get_params['name'])?$get_params['name']:'';
    $mobile_no = isset($get_params['mno'])?$get_params['mno']:'';
    $email = isset($get_params['Email'])?$get_params['Email']:'';
    $address = isset($get_params['Address'])?$get_params['Address']:'';
    $pincode = isset($get_params['pincode'])?$get_params['pincode']:'';
    $state = isset($get_params['state'])?$get_params['state']:'';
    $cata = isset($get_params['cata'])?$get_params['cata']:'';

    $company_name = isset($post_params['company_name'])?$post_params['company_name']:'';
    $disput_amount = isset($post_params['amount'])?$post_params['amount']:0;
    $complaint_details = isset($post_params['complaint'])?$post_params['complaint']:'';
    $price_range = isset($post_params['price_range'])?$post_params['price_range']:0;

    //get the price of the complained
    $complain_amount = complain_prices($price_range);

    if(!is_array($complain_amount) && $complain_amount>0){
        $registration_id = "CUSCOM-".date("Y")."/".rand(9999,1000000);
        //now get only the files 
        //$files = array_values($file_params);
        $subject="Customer try to payment for complain Id ".$registration_id;
        $message = "Hi,\nCustomer fill up a complain, details are as follows\n";
        $message .="\nCustomer Name : ".$name;
        $message .="\nCustomer Phone : ".$mobile_no;
        $message .="\nCustomer Email : ".$email;
        $message .="\nCustomer Address : ".$address;
        $message .="\nCustomer PIN : ".$pincode;
        $message .="\nState : ".$state;
        $message .="\nComplain Category : ".$cata;
        $message .="\nCompany Name: ".$company_name;
        $message .="\Disput Amount: ".$disput_amount;
        $message .="\nComplain Details : ".$complaint_details;
        $message .="\nComplaint ID : ".$registration_id;
        $message .="\nComplain Amount : ".$complain_amount;
        $headers = mail_headers();
        mail(recieverEmail,$subject,$message,$headers);
        
        //multi_attach_mail(recieverEmail,$subject,$message,$files);

        //validate the payment section and go to the payment PG
        $total_payment  = $complain_amount + ceil(($complain_amount*18)/100);
        //now make payment for this application
        $amout=$total_payment;
        $purpose="Complaint Id ".$registration_id;
        $buyer_name = $name;
        $phone = $mobile_no;
        $pay_for=6;
        $payment_request_url = get_payment_link($amout,$purpose,$buyer_name,$phone,$email,$pay_for);
        header("Location:$payment_request_url");
    }
    else{
        die("Error :: Invalid request");
    }
}

function complain_prices($compainType=0){
    $prices=[
        '1'=>'499',
        '2'=>'799',
        '3'=>'1299'
    ];
    if(!empty($compainType)){
        return (isset($prices[$compainType]))?$prices[$compainType]:0;
    }
    else{
        return $prices;
    }
}

function multi_attach_mail($to, $subject, $message, $files = array(),$isFormFile=true){

    //$from = $senderName." <".$senderEmail.">";  
    //$headers = "From: $from"; 
    $headers = mail_headers();
    // Boundary  
    $semi_rand = md5(time());  
    $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";  
 
    // Headers for attachment  
    $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mime_boundary}\"";  
 
    // Multipart boundary  
    $message = "--{$mime_boundary}\n" . "Content-Type: text/html; charset=\"UTF-8\"\n" . 
    "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";  
 
    // Preparing attachment 
    if(!empty($files)){ 
        if($isFormFile){
            foreach($files as $file){
                $file_name = $file['name']; 
                $file_size = $file['size']; 
                $filepath = $file['tmp_name'];
                $message .= "--{$mime_boundary}\n"; 
                $fp =    @fopen($filepath, "rb"); 
                $data =  @fread($fp, $file_size); 
                @fclose($fp); 
                $data = chunk_split(base64_encode($data)); 
                $message .= "Content-Type: application/octet-stream; name=\"".$file_name."\"\n" .  
                "Content-Description: ".$file_name."\n" . 
                "Content-Disposition: attachment;\n" . " filename=\"".$file_name."\"; size=".$file_size.";\n" .  
                "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n"; 
            }
        }
        else{
            for($i=0;$i<count($files);$i++){ 
                if(is_file($files[$i])){ 
                    $file_name = basename($files[$i]); 
                    $file_size = filesize($files[$i]); 
                     
                    $message .= "--{$mime_boundary}\n"; 
                    $fp =    @fopen($files[$i], "rb"); 
                    $data =  @fread($fp, $file_size); 
                    @fclose($fp); 
                    $data = chunk_split(base64_encode($data)); 
                    $message .= "Content-Type: application/octet-stream; name=\"".$file_name."\"\n" .  
                    "Content-Description: ".$file_name."\n" . 
                    "Content-Disposition: attachment;\n" . " filename=\"".$file_name."\"; size=".$file_size.";\n" .  
                    "Content-Transfer-Encoding: base64\n\n" . $data . "\n\n"; 
                } 
            } 
        }
        
    }
     
    $message .= "--{$mime_boundary}--"; 
    //$returnpath = "-f" . $senderEmail; 
     
    // Send email 
    @mail($to, $subject, $message, $headers);
}

//RTI section 
//rti application basic information 
function rti_basic_info($data){
    common_print($data);
    //create random a Registration ID 
    $registration_id = "LTRTI-".date("Y")."/".rand(9999,1000000);
    $subject="Basic details of RTI application";
    $message = "Hi,\nCustomer fill RTI allication, details are as follows\n";
    $message .="\nApplicant Name : ".$data['name'];
    $message .="\nFather\'s Name : ".$data['fname'];
    $message .="\nApplicant Phone : ".$data['mno'];
    $message .="\nApplicant Email : ".$data['Email'];
    $message .="\nApplicant Address : ".$data['address'];
    $message .="\nStates: ".$data['states'];
    $message .="\nPIN : ".$data['pin'];
    $message .="\nName of Govt. Department : ".$data['dept_name'];
    $message .="\nDescription of The Topic : ".$data['topic'];
    $message .="\n\Registration Id : ".$registration_id;
    //mail function of the server default called for send the mail with sunject 
    $headers = mail_headers();
    mail(recieverEmail,$subject,$message,$headers);
    $msg='';
    return_response($status=1,$msg,array('registration_id'=>$registration_id));
}

//payment section for rti 
function rti_payment_request($data){
    common_print($data);
    $res_data=array();
    $msg="";
    $total_payment='299';
    $is_urgent_work = $data['urgent_work'];
    $subject="RTI Application for ".$data['states'].', '.$data['pin'];
    $subject="RTI Application ID ".$data['registration_id'];
    if($is_urgent_work){
        $total_payment=353;
        $subject.=". Urgent work.";
    }
    //apply 18% chrge 
    $total_payment  = $total_payment + ceil(($total_payment*18)/100);
    //now make payment for this application
    $amout=$total_payment;
    $purpose=$subject;
    $buyer_name = $data['name'];
    $phone = $data['mno'];
    $email = $data['Email'];
    $pay_for=3;
    $payment_request_url = get_payment_link($amout,$purpose,$buyer_name,$phone,$email,$pay_for);
    $res_data['url']=$payment_request_url;
    return_response($status=1,$msg,$res_data);
}

//end rti section
//GST section
function gst_basic_info($data){
    common_print($data);
    $subject="Customer looking for GST registration";
    $message = "Hi,\nCustomer contact details are as follows\n";
    $message .="\nCustomer Name : ".$data['name'];
    $message .="\nCustomer Phone : ".$data['phone'];
    $message .="\nCustomer Email : ".$data['email'];
    //mail function of the server default called for send the mail with sunject 
    $headers = mail_headers();
    mail(recieverEmail,$subject,$message,$headers);
    $msg='';
    return_response($status=1,$msg,$data=array());
} 
//gst full pplication 
function gst_application($data){
    common_print($data);
    $file_params = $_FILES;
    //now get only the files 
    $files = array_values($file_params);
    $subject="Customer filling GST registration form";
    $message = "Hi,\nGST registration form details are as follows\n";
    $message .="\nCustomer Name : ".$_POST['name'];
    $message .="\nCustomer Phone : ".$_POST['phone'];
    $message .="\nCustomer Email : ".$_POST['email'];
    $message .="\nCustomer Gender : ".$_POST['gender'];
    $message .="\nCustomer Designation: ".$_POST['Designation'];
    $message .="\nCustomer Business : ".$_POST['Business'];
    $message .="\nBusiness Nature : ".$_POST['Nature_of_Business'];
    $message .="\nState : ".$_POST['state'];
    $message .="\nLand Type : ".$_POST['landtype'];
    $gst_charge = $_POST['gst_charge'];

    $apply_gst_charge = 0;
    if($gst_charge==2){
        $apply_gst_charge ='1499';
        $message .="\nGST Scheme : GST Registration + 3 Months GST Return Filing (INR ".$apply_gst_charge.")";
    }
    elseif($gst_charge==3){
        $apply_gst_charge ='5999';
        $message .="\nGST Scheme : GST Registration + 12 Months GST Return Filing (INR ".$apply_gst_charge.")";
    }
    else{
        $apply_gst_charge ='899';
        $message .="\nGST Scheme : GST Registration (INR ".$apply_gst_charge.")";
    }
    
    multi_attach_mail(recieverEmail,$subject,$message,$files);

    //now make payment for this application
    $amout=$apply_gst_charge;
    $purpose=$subject;
    $buyer_name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $pay_for=2;
    $payment_request_url = get_payment_link($amout,$purpose,$buyer_name,$phone,$email,$pay_for);
    header("Location:$payment_request_url");
}

function gst_payment($data){
    common_print($data);
    $payment_request_url="index.html";
    $data = $_POST;
    if(isset($data['registration_id']) && !empty($data['registration_id'])){
        $gst_charge = (!empty($data['price_range']))?$data['price_range']:0;
        $name = (!empty($data['name']))?$data['name']:'';
        $email = (!empty($data['email']))?$data['email']:'';
        $phone = (!empty($data['phone']))?$data['phone']:'';

        $apply_gst_charge=0;
        
        if($gst_charge==2){
            $apply_gst_charge ='3999';
            $subject="GST STANDARD Plan.";
        }
        elseif($gst_charge==3){
            $apply_gst_charge ='7899';
            $subject="GST PREMIUM Plan.";
        }
        else{
            $apply_gst_charge ='1699';
            $subject="GST BASIC Plan.";
        }
        $subject .=" Application ID ".$data['registration_id'];
        if($apply_gst_charge>0){
            $amout=$apply_gst_charge;
            $purpose=$subject;
            $buyer_name = $name;
            $phone = $phone;
            $email = $email;
            $pay_for=3;
            $payment_request_url = get_payment_link($amout,$purpose,$buyer_name,$phone,$email,$pay_for);
        }
    }
    header("Location:$payment_request_url");
}

function gst_payment_request($data){
    common_print($data);
    //create random a Registration ID 
    $registration_id = "LTGST-".date("Y")."/".rand(9999,1000000);
    $maildata = $data;
    $subject="Customer Filling GST Registration Form";
    $message = "Hi,\nGST Registration form details are as follows\n";
    $message .="\nName : ".$maildata['name'];
    $message .="\nPhone : ".$maildata['phone'];
    $message .="\nEmail : ".$maildata['email'];
    $message .="\nAddress : ".$maildata['address'];
    $message .="\nState : ".$maildata['state'];
    $message .="\nPIN : ".$maildata['pin_code'];
    $message .="\nCompany Name : ".$maildata['company_name'];
    $message .="\nGST No. : ".$maildata['gst_no'];
    
    $message .="\n\Registration Id : ".$registration_id;

    $gst_charge = (!empty($maildata['price_range']))?$maildata['registration_id']:0;
    $apply_gst_charge=0;
    if($gst_charge==2){
        $apply_gst_charge ='3999';
        $message .="\nGST Scheme : STANDARD 6 month GST return filing with GST registration (INR ".$apply_gst_charge.")";
    }
    elseif($gst_charge==3){
        $apply_gst_charge ='7899';
        $message .="\nGST Scheme : PREMIUM 12 month GST return filing with GST registration (INR ".$apply_gst_charge.")";
    }
    else{
        $apply_gst_charge ='1699';
        $message .="\nGST Scheme : BASIC 3 month GST return filing (INR ".$apply_gst_charge.")";
    }

    //mail function of the server default called for send the mail with sunject 
    $headers = mail_headers();
    mail(recieverEmail,$subject,$message,$headers);
    $msg='Thanks you, we will contact with you very soon.';
    return_response($status=1,$msg,array('registration_id'=>$registration_id));
}
// end GST
//udyog adhar registration section 
function uar_basic_info($data){
    common_print($data);
    //create random a Registration ID 
    $registration_id = "LTMSME-".date("Y")."/".rand(9999,1000000);
    $maildata = $data;
    $subject="Customer Filling Udyog Aadhaar Registration Form";
    $message = "Hi,\nUdyog Aadhaar Registration form details are as follows\n";
    $message .="\nApplicant Name : ".$maildata['name'];
    $message .="\nApplicant Phone : ".$maildata['mobileno'];
    $message .="\nApplicant Email : ".$maildata['Email'];
    $message .="\nApplicant Aadhaar : ".$maildata['aadhaar'];
    $message .="\nSocial Category : ".$maildata['socialcategory'];
    $message .="\nApplicant Gender : ".$maildata['gender'];
    $message .="\nBusiness Name : ".$maildata['business'];
    $message .="\nBusiness Type : ".$maildata['organisationtype'];
    $message .="\nBusiness Activity : ".$maildata['businessactivity'];
    $message .="\nPAN : ".$maildata['PAN'];
    $message .="\nBank A/C : ".$maildata['acno'];
    $message .="\nBank IFSC Code : ".$maildata['IFSCCode'];
    $message .="\n\Registration Id : ".$registration_id;
    //mail function of the server default called for send the mail with sunject 
    $headers = mail_headers();
    mail(recieverEmail,$subject,$message,$headers);
    $msg='Thanks you, we will contact with you very soon.';
    return_response($status=1,$msg,array('registration_id'=>$registration_id));

}
//payment section
function msme_payment_request($data){
    common_print($data);
    $res_data=array();
    $msg="";
    $total_payment='999';
    $subject="MSME Application ID ".$data['registration_id'];
    
    //apply 18% chrge 
    //$total_payment  = $total_payment + ceil(($total_payment*18)/100);
    //now make payment for this application
    $amout=$total_payment;
    $purpose=$subject;
    $buyer_name = $data['name'];
    $phone = $data['mobileno'];
    $email = $data['Email'];
    $pay_for=5;
    $payment_request_url = get_payment_link($amout,$purpose,$buyer_name,$phone,$email,$pay_for);
    $res_data['url']=$payment_request_url;
    return_response($status=1,$msg,$res_data);
}
// end uar
// digital signature certificate
// first form details before payment page
function customer_dsc_enquery($data){
    $get_params = $_GET;
    $subject="Digital signature certificate enquiry";
    $message = "Hi,\nCustomer looking for digital signature certificate, details are as follows\n";
    if(isset($_GET['is_category_payment']) && $_GET['is_category_payment']=='1'){
        $message .=$_GET['plane_name'];
    }
    else{
        if(isset($_GET['clt'])){
            $message .=$_GET['clt'].", ".$_GET['crt']." - ".$_GET['ust'].", ".$_GET['ufor']." with ".$_GET['vld']." validity.";
        }
    }
    
    $message .="\nCustomer Name : ".$data['name'];
    $message .="\nCustomer Phone : ".$data['phone'];
    $message .="\nCustomer Email : ".$data['email'];
    $message .="\nCertificate Quantity : ".$data['quantity'];
    //mail function of the server default called for send the mail with sunject 
    $headers = mail_headers();
    mail(recieverEmail,$subject,$message,$headers);
    $msg='';
    return_response($status=1,$msg,$data=array());
}

// payment request for dsc 
function dsc_payment($data){
    common_print($data);
    //receive datas 
    $get_params = $_GET;
    $post_params = $_POST;
    $purpose = "";
    $paying_amount=0;
    $status=1;
    $message="";
    //now get all the posted value 
    $all_form_value = isset($_POST['all_form_value'])?$_POST['all_form_value']:'';
    if(!empty($all_form_value)){
        $all_form_value = json_decode($all_form_value,true);
    }
    else{
        $status=0;
        $message="invalid access";
    }
    $name = isset($all_form_value['name'])?$all_form_value['name']:'';
    $email = isset($all_form_value['email'])?$all_form_value['email']:'';
    $phno = isset($all_form_value['phno'])?$all_form_value['phno']:'';
    $quantity = isset($all_form_value['quantity'])?$all_form_value['quantity']:'1';
    
    if(isset($all_form_value['is_category_payment']) && $all_form_value['is_category_payment']=='1'){
        $purpose = isset($all_form_value['plane_name'])?$all_form_value['plane_name']:'';
        $payment_id = isset($all_form_value['payment_id'])?$all_form_value['payment_id']:'';
        $payment_amount = isset($all_form_value['payment_amount'])?$all_form_value['payment_amount']:'';
        //validate the payment id with payment price
        $basic_amount = get_category_paying_amount($payment_id);
        if($basic_amount == $payment_amount){
            $paying_amount =  $basic_amount*$quantity;
        }
        else{
            $status=0;
            $message="Amount missmatched";
        }
    }
    elseif(isset($all_form_value['ufor'])){
        $ufor = isset($all_form_value['ufor'])?$all_form_value['ufor']:'';
        $clt = isset($all_form_value['clt'])?$all_form_value['clt']:'';
        $ust = isset($all_form_value['ust'])?$all_form_value['ust']:'';
        $vld = isset($all_form_value['vld'])?$all_form_value['vld']:'';
        $crt = isset($all_form_value['crt'])?$all_form_value['crt']:'';
        $payment_amount = isset($all_form_value['amount'])?$all_form_value['amount']:'0';
        $purpose=$clt.", ".$crt." - ".$ust.", ".$ufor." with ".$vld." validity.";
        //validate the payment amount and the price 
        $keyStr = trim($ufor)."_".trim($vld)."_".trim($crt);
        //now replace all 
        $keyStr = str_replace(array(" ","(","-"),"_",$keyStr);
        $keyStr = str_replace(")","",$keyStr);
        $keyStr = strtolower($keyStr);
        $basic_amount = get_digital_certificate_prices($keyStr);
        if($basic_amount == $payment_amount){
            $paying_amount =  $basic_amount*$quantity;
        }
        else{
            $status=0;
            $message="Amount missmatched";
        }
    }
    
    //now need to go for payment gateway 
    if($status){
        //now make payment for this application
        $amout=$paying_amount;
        $buyer_name = $name;
        $phone = $phno;
        $email = $email;
        $pay_for=1;
        $payment_request_url = get_payment_link($amout,$purpose,$buyer_name,$phone,$email,$pay_for);
        header("Location:$payment_request_url");
    }
    else{
        //go to error page 
        die("Error :: ".$message);
        //$baseurl = get_base_url();
        //$payment_request_url = $baseurl."backend.php?func=payment_request_faild&errors=$message";
       // header("Location:$payment_request_url");
    }
}

//payment request for dsc category view card
function dci_category_payment($data){
    $payment_id = $data['payment_id'];
    $status=0;
    $msg="invalid payment card choosed";
    $resdata=[];
    if($payment_id>0){
        $apply_gst_charge="799";
        $subject = "Class3 Digital Signature For GST with 2 Years validity, along with USB Token.";
        //TODO we can define the details of the payment related info

        //called to get payment url
        $amout=$apply_gst_charge;
        $purpose=$subject;
        $buyer_name="";
        $phone="";
        $email="";
        $pay_for=4;
        $payment_request_url = get_payment_link($amout,$purpose,$buyer_name,$phone,$email,$pay_for);
        $resdata['url']=$payment_request_url;
        $status=1;
    }
    return_response($status,$msg,$resdata);
}

function get_category_payment_amount($data){
    $payment_id = $data['payment_id'];
    $status=0;
    $url = get_base_url()."dsc_form1.html";
    $payment_amount = get_category_paying_amount($payment_id);
    if($payment_amount>0){
        $status=1;
    }
    return_response($status,$msg="amount of this category",['payment_amount'=>$payment_amount,'url'=>$url]);
}
//category id payment config
function get_category_paying_amount($payment_id=0){
    $category_payments=[
        '1'=>'999',
        '2'=>'2599',
        '3'=>'2599',
        '4'=>'677',
        '5'=>'799',
        '6'=>'899',
        '7'=>'677',
        '8'=>'799',
        '9'=>'899',
        '10'=>'677',
        '11'=>'799',
        '12'=>'899',
        '13'=>'677',
        '14'=>'799',
        '15'=>'899',
    ];
    return (isset($category_payments[$payment_id]))?$category_payments[$payment_id]:0;
}

// payment request for gst category card
function gst_category_payment($data){
    $payment_id = $data['payment_id'];
    $status=0;
    $msg="invalid payment card choosed";
    $resdata=[];
    if($payment_id>0){
        $get_pay_link=true;
        switch($payment_id){
            case 1:
                $apply_gst_charge="899";
                $subject = "BASIC GST Registration";
            break;
            case 2:
                $apply_gst_charge="899";
                $subject = "STANDARD GST Registration";
            break;
            case 3:
                $apply_gst_charge="899";
                $subject = "PREMIUM GST Registration";
            break;
            default:
                $get_pay_link=false;
        }
        if($get_pay_link){
            //called to get payment url
            $amout=$apply_gst_charge;
            $purpose=$subject;
            $buyer_name="";
            $phone="";
            $email="";
            $pay_for=4;
            $payment_request_url = get_payment_link($amout,$purpose,$buyer_name,$phone,$email,$pay_for);
            $resdata['url']=$payment_request_url;
            $status=1;
        }
    }
    return_response($status,$msg,$resdata);
}

// feedback section
function feedback($data){
    common_print($data);
    $subject="Feedback from customer";
    $message = "Hi,\nCustomer give a feedback details are as follows\n";
    $message .="\nCustomer Name : ".$data['name'];
    $message .="\nCustomer Phone : ".$data['mobileno'];
    $message .="\nCustomer Email : ".$data['email'];
    $message .="\nSubject : ".$data['subject'];
    $message .="\nMessage : ".$data['message'];
    $headers = mail_headers();
    mail(recieverEmail,$subject,$message,$headers);
    $msg='Thank you, Your feedback is valuable for us';
    return_response($status=1,$msg,$data=array());
}

$functionName = (isset($_GET['func']))?$_GET['func']:'';
if(!empty($functionName)){
    if(function_exists($functionName)){
        $inputdata = file_get_contents('php://input');
        $data = json_decode($inputdata, true);
        $functionName($data);
    }
    else{
        return_response($status=0,$message="not found",$extradata=array());
    }
}

function common_print($data){
    if(printLog){
        $get_params = $_GET;
        $post_params = $_POST;
        echo "</br>GET ::</br> ";
        print_r($get_params);
        echo "</br>POST ::</br>";
        print_r($post_params);
        echo "</br>JSON</br>";
        print_r($data);
        echo "</br>=======</br>";
    }
}

function return_response($status=1,$message,$extradata=array()){
    $returndata=array(
        'status'=>$status,
        'messages'=>$message,
    );
    if(is_array($extradata) && !empty($extradata)){
        $returndata  = array_merge($returndata,$extradata);
    }
    die(json_encode($returndata));
}

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

function get_payment_link($amount=0, $purpose="",$buyer_name="",$phone="",$email="",$payment_for=1){
    $payment_request_url="";
    if($amount>0 && !empty($purpose)){
        $root = get_base_url();
        $baseurl= $root."backend.php";
        $redirect_url="$baseurl?func=payment_gateway_return&payment_for=$payment_for";
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
    //
    $localLogOn=false;
	$payment_status = $_GET['payment_status'];
	$payment_id = $_GET['payment_id'];
	$payment_request_id = $_GET['payment_request_id'];
	$payment_for = $_GET['payment_for']; // to handle the form data value from local browser memory
	$responseDatas=array(
		'payment_status'=>$payment_status,
		'payment_id'=>$payment_id,
		'payment_request_id'=>$payment_request_id,
    );
    if($localLogOn){
        echo "</br><pre>";
        print_r($responseDatas);
        echo "</br></pre>";
    }
	
	// now checke the status of the payment request
	$endPoint="payment-requests/$payment_request_id";
    $response = call_curl($endPoint,$payload=array(),$mode="get");
    if($localLogOn){
        echo "</br><pre>";
        print_r($response);
        echo "</br></pre>";
    }
	
    // gel all teatails 
    $subject="";
    $message="";
    $headers = mail_headers();
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
    mail(recieverEmail,$subject,$message,$headers);
    //now redirect to success page
    $params = "?payment_status=$payment_status&payment_id=$payment_id&payment_request_id=$payment_request_id&payment_for=$payment_for";
    switch($payment_for){
        case 1: //dc page 
            $payment_return_url="dsc.html";
        break;
        case 2:
            $payment_return_url="gst.html";
        break;
        case 3:
            $payment_return_url="rti.html";
        break;
        case 4: //dsc category payment
            $payment_return_url="dsc.html";
        break;
        default:
            $payment_return_url="index.html";
    }
    $payment_return_url = "succes.html";
    $payment_return_url .=$params;
    
    header("Location:$payment_return_url");
}

// inter server call
function payment_gateway_webhook(){
    $data = $_POST;
    $mac_provided = $data['mac'];  // Get the MAC from the POST data
    unset($data['mac']);  // Remove the MAC key from the data.
    $ver = explode('.', phpversion());
    $major = (int) $ver[0];
    $minor = (int) $ver[1];
    if($major >= 5 and $minor >= 4){
        ksort($data, SORT_STRING | SORT_FLAG_CASE);
    }
    else{
        uksort($data, 'strcasecmp');
    }
    // You can get the 'salt' from Instamojo's developers page(make sure to log in first): https://www.instamojo.com/developers
    // Pass the 'salt' without <>
    $mac_calculated = hash_hmac("sha1", implode("|", $data), "<YOUR_SALT>");
    if($mac_provided == $mac_calculated){
        if($data['status'] == "Credit"){
            // Payment was successful, mark it as successful in your database.
            // You can acess payment_request_id, purpose etc here. 
        }
        else{
            // Payment was unsuccessful, mark it as failed in your database.
            // You can acess payment_request_id, purpose etc here.
        }
    }
    else{
        echo "MAC mismatch";
    }
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

//get digital signeture types
function get_certificate_prices(){
    $certificate_prices = get_digital_certificate_prices();
    $msg="certificate price details";
    return_response($status=1,$msg,['certificate_prices'=>$certificate_prices]);
}

function get_digital_certificate_prices($scheme_name=''){
    $certificate=[
        "for_multiple_purpose_1_year_signature"=>'899',
        "for_multiple_purpose_1_year_encrypted"=>'899',
        "for_multiple_purpose_1_year_both"=>'1798',
        "for_multiple_purpose_2_year_signature"=>'999',
        "for_multiple_purpose_2_year_encrypted"=>'999',
        "for_multiple_purpose_2_year_both"=>'1989',

        "for_gst_1_year_signature"=>'150',
        "for_gst_1_year_encrypted"=>'150',
        "for_gst_1_year_both"=>'300',
        "for_gst_2_year_signature"=>'250',
        "for_gst_2_year_encrypted"=>'250',
        "for_gst_2_year_both"=>'500',
        
        "for_mca_roc_1_year_signature"=>'200',
        "for_mca_roc_1_year_encrypted"=>'200',
        "for_mca_roc_1_year_both"=>'400',
        "for_mca_roc_2_year_signature"=>'300',
        "for_mca_roc_2_year_encrypted"=>'300',
        "for_mca_roc_2_year_both"=>'600',
        
        "for_epf_1_year_signature"=>'250',
        "for_epf_1_year_encrypted"=>'250',
        "for_epf_1_year_both"=>'350',
        "for_epf_2_year_signature"=>'350',
        "for_epf_2_year_encrypted"=>'350',
        "for_epf_2_year_both"=>'650',
        
        "for_income_tax_1_year_signature"=>'400',
        "for_income_tax_1_year_encrypted"=>'400',
        "for_income_tax_1_year_both"=>'700',
        "for_income_tax_2_year_signature"=>'450',
        "for_income_tax_2_year_encrypted"=>'450',
        "for_income_tax_2_year_both"=>'850',
        
        "for_e_tender_1_year_signature"=>'250',
        "for_e_tender_1_year_encrypted"=>'250',
        "for_e_tender_1_year_both"=>'450',
        "for_e_tender_2_year_signature"=>'350',
        "for_e_tender_2_year_encrypted"=>'350',
        "for_e_tender_2_year_both"=>'650',
         
        "for_document_signer_1_year_signature"=>'150',
        "for_document_signer_1_year_encrypted"=>'200',
        "for_document_signer_1_year_both"=>'300',
        "for_document_signer_2_year_signature"=>'200',
        "for_document_signer_2_year_encrypted"=>'250',
        "for_document_signer_2_year_both"=>'400',
        
        "for_director_kyc_1_year_signature"=>'300',
        "for_director_kyc_1_year_encrypted"=>'400',
        "for_director_kyc_1_year_both"=>'650',
        "for_director_kyc_2_year_signature"=>'250',
        "for_director_kyc_2_year_encrypted"=>'300',
        "for_director_kyc_2_year_both"=>'450',
    ];

    //find the price 
    if(!empty($scheme_name)){
        return (isset($certificate[$scheme_name]))?$certificate[$scheme_name]:'0.00';
    }
    else{
        return $certificate;
    }
}
?>