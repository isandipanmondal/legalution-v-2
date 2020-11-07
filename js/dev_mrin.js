'use strict'
var baseUrl="";
$(document).ready(function(){
    getBasePath();
    talk_io_widget();
    console.log(baseUrl);
    //CUSTOMER INQUERY SECTION
    //validator with ajax call for details inquery of services
    $("#detail_inquery_frm").validate({
        rules:{
            yn:{
                required:true,
            },
            eid:{
                required:true,
                email:true,
            },
            mob:{
                required:true,
                digits:true,
                minlength:10,
                maxlength:10,
            }
        },
        messages:{
            yn:{
                required:'',
            },
            eid:{
                required:'',
                email:'',
            },
            mob:{
                required:'',
                digits:'',
                minlength:'',
                maxlength:'',
            }
        },
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
        submitHandler:function(form){
            let path = "backend.php?func=detail_enquery";
            let data={
                name:$("#yn").val(),
                email:$("#eid").val(),
                phone:$("#mob").val(),
                service:$("#serv").val(),
            };
            doAjaxCall(path,data,function(response){
                if(!response.error){
                    //reset the form
                    form.reset();
                }
                alert(response.msg);
            });
        }
    });

    //validate with ajax call for callback request for service details
    $("#calback_request").validate({
        rules:{
            call_name:{
                required:true,
                maxlength:100,
            },
            call_phone:{
                required:true,
                digits:true,
                minlength:10,
                maxlength:10,
            }
        },
        messages:{
            call_name:{
                required:'',
                maxlength:'',
            },
            call_phone:{
                required:'',
                digits:'',
                minlength:'',
                maxlength:'',
            }
        },
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
        submitHandler:function(form){
            let path = "backend.php?func=callback_request";
            let data={
                name:$("#call_name").val(),
                phone:$("#call_phone").val(),
            };
            doAjaxCall(path,data,function(response){
                alert(response.msg);
                if(!response.error){
                    //reset the form
                    form.reset();
                }
            });
        }
    });
    //END :: CUSTOMER INQUERY

    //DIGITAL SIGNATURE SECTION
    $("#sbmt").click(function(e){
        e.preventDefault();
        $("#frm_dsc").submit();
    });
    //validate the form value and submit
    $("#frm_dsc").validate({
        rules:{
            ufor:{
                required:true,
            }
        },
        messages:{
            ufor:{
                required:'',
            }
        },
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
        submitHandler:function(form){
            //need to get all value of the form 
            let frmData = $(form).serializeArray();
            let dsc_frm_key_value={};
            $.each(frmData,function(i,item){
                dsc_frm_key_value[item.name] = item.value;
            });
            // cleare browser data
            cleareData('dsc_frm_data');
            cleareData('dsc_frm_key_value');
            //saving the form values
            doSaveData('dsc_frm_data',frmData);
            doSaveData('dsc_frm_key_value',dsc_frm_key_value);
            //form key values 
            form.submit();
        }
    });

    // basic details form of customer :: form 1
    $("#dsc_form_1").validate({
        rules:{
            name:{
                required:true,
                maxlength:100,
            },
            email:{
                required:true,
                email:true,
            },
            phno:{
                required:true,
                digits:true,
                minlength:10,
                maxlength:10,
            },
            quantity:{
                required:true,
                digits:true,
                min:1,
            }
        },
        messages:{},
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
        submitHandler:function(form){
            //saving the data into the storage 
            let new_frmData = $(form).serializeArray();
            let old_dsc_frm_data = getSavedData('dsc_frm_data');
            //merge old and new one 
            let dsc_frm_key_value={};
            let frmData = $.merge(new_frmData,old_dsc_frm_data);
            console.log(frmData);
            $.each(frmData,function(i,item){
                dsc_frm_key_value[item.name] = item.value;
            });
            //saving
            doSaveData('dsc_frm_data',frmData);
            doSaveData('dsc_frm_key_value',dsc_frm_key_value);

            //update the form get value and travel to the next page
            let search="";
            $.each(old_dsc_frm_data,function(i,item){
                if(search.length>0){
                    search +='&'+item.name+'='+item.value;
                }
                else{
                    search=item.name+'='+item.value;
                }
            });
            $("#dsc_frm_val").val(location.search.replace("?",""));
            let path = "backend.php"+location.search+'&func=customer_dsc_enquery';
            if(search.length>0){
                $("#dsc_frm_val").val(search);
                path = "backend.php?"+search+'&func=customer_dsc_enquery';
            }
            
            // get all the dsc plane details
            
            let data={
                name:$("#name").val(),
                email:$("#email").val(),
                phone:$("#phno").val(),
                quantity:$("#quantity").val(),
            };

            doAjaxCall(path,data,function(response){
                if(!response.error){
                    //reset the form
                    form.submit();
                }
            });
        }
    });

    // customer address details :: form 2 
    $("#dsc_form_2").validate({
        rules:{
            address:{
                //required:true,
                maxlength:200,
            },
            state:{
                //required:true,
            },
            city:{
                //required:true,
                maxlength:100,
            },
            pincode:{
                //required:true,
                digits:true,
                minlength:6,
                maxlength:6,
            }
        },
        messages:{
            
        },
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
        submitHandler:function(form){
            //saving the data into the storage 
            let new_frmData = $(form).serializeArray();
            let old_dsc_frm_data = getSavedData('dsc_frm_data');
            //merge old and new one 
            let dsc_frm_key_value={};
            let frmData = $.merge(new_frmData,old_dsc_frm_data);
            console.log(frmData);
            $.each(frmData,function(i,item){
                dsc_frm_key_value[item.name] = item.value;
            });
            //saving 
            doSaveData('dsc_frm_data',frmData);
            doSaveData('dsc_frm_key_value',dsc_frm_key_value);
            // now save all the value  as json string for post 
            $("#all_form_value").val(JSON.stringify(dsc_frm_key_value));
            form.submit();
        }
    });

    //END :: DIGITAL SIGNITURE

    //COMPLAIN SECTION
    //basic details of complain 
    $("#complainFrm1").validate({
        rules:{
            name:{
                required:true,
                maxlength:100,
            },
            mno:{
                required:true,
                digits:true,
                minlength:10,
                maxlength:10,
            },
            Email:{
                required:true,
                email:true,
            },
            Address:{
                //required:true,
                maxlength:250,
            },
            pincode:{
                //required:true,
                digits:true,
                maxlength:6,
            },
            cata:{
                //required:true,
                maxlength:250,
            },
        },
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
        submitHandler:function(form){
            // maild basic details of the user who filled the data
            let frmdata = $(form).serializeArray();
            //now save all the form data into the storage 
            let complain_form_key_value={};
            $.each(frmdata,function(i,item){
                complain_form_key_value[item.name] = item.value;
            });
            cleareData('complain_form_key_value');
            doSaveData('complain_form_key_value',complain_form_key_value);
            //how send send email about this complain
            let path = "backend.php?func=complaine_basic_info";
            doAjaxCall(path,complain_form_key_value,function(response){
                form.submit();
            });
        }
    });

    //customer complaine form 2
    $("#complainFrm2").validate({
        rules:{
            company_name:{
                //required:true,
                maxlength:250,
            },
            amount:{
                digits:true,
            },
            complaint:{
                maxlength:350
            }
        },
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
        submitHandler:function(form){
            //validate if the price range checked 
            if($("input[name='price_range']:checked").length > 0){
                //update the url of the form 
                let frmaction = $(form).attr('action')+location.search+'&func=customer_complain';
                $(form).attr('action',frmaction);
                form.submit();
            }
            else{
                alert("Please choose your disputs amounts range.");
            }
            
        }
    });

    //END :: COMPLAIN

    //REGISTRATION AND FILLING SECTION 
    // GST registrations basic details of customer 
    $("#gst_form_basic_info").validate({
        rules:{
            name:{
                required:true,
                maxlength:100,
            },
            email:{
                required:true,
                email:true,
            },
            phone:{
                required:true,
                digits:true,
                minlength:10,
                maxlength:10,
            },
        },
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
        submitHandler:function(form){
            // maild basic details of the user who filled the data
            let frmdata = $(form).serializeArray();
            //now save all the form data into the storage 
            let gst_form_key_value={};
            $.each(frmdata,function(i,item){
                gst_form_key_value[item.name] = item.value;
            });
            cleareData('gst_form_key_value');
            doSaveData('gst_form_key_value',gst_form_key_value);
            //how send send email about this complain
            let path = "backend.php?func=gst_basic_info";
            doAjaxCall(path,gst_form_key_value,function(response){
                form.submit();
            });
        }
    });

    //GST application form validation 
    $("#gst_application_form").validate({
        rules:{
            name:{
                required:true,
                maxlength:100,
            },
            phone:{
                required:true,
                digits:true,
                minlength:10,
                maxlength:10,
            },
            email:{
                required:true,
                email:true,
            },
            gst_charge:{
                required:true
            }
        },
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
    });
    // gst payment package request
    $("#gst_payment_request").validate({
        rules:{
            name:{
                required:true,
                maxlength:100,
            },
            phone:{
                required:true,
                digits:true,
                minlength:10,
                maxlength:10,
            },
            email:{
                required:true,
                email:true,
            },
            /*price_range:{
                required:true
            }*/
        },
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
        submitHandler:function(form){
            // maild basic details of the user who filled the data
            let frmdata = $(form).serializeArray();
            //now save all the form data into the storage 
            let gst_payment_form_key_value={};
            $.each(frmdata,function(i,item){
                gst_payment_form_key_value[item.name] = item.value;
            });
            if(!gst_payment_form_key_value.hasOwnProperty('price_range')){
                alert("Please choose a plan");
            }
            else{
                let val = gst_payment_form_key_value.price_range;
                let alt_msg="You choosed ";
                if(val==3){
                    alt_msg +=" PREMIUM Plan.";
                }
                else if(val==2){
                    alt_msg +=" STANDARD Plan.";
                }
                else{
                    alt_msg +=" BASIC Plan.";
                }
                if(confirm(alt_msg)){
                    //how send send email about this complain
                    let path = "backend.php?func=gst_payment_request";
                    doAjaxCall(path,gst_payment_form_key_value,function(response){
                        console.log(response);
                        if(!response.error){
                            $("#registration_id").val(response.full_res.registration_id);
                            form.submit();
                        }
                    });
                }
            }
        }
    });

    //udyog aadhar registration
    $("#uar_form").validate({
        rules:{
            name:{
                required:true,
                maxlength:100,
            },
            mobileno:{
                required:true,
                digits:true,
                minlength:10,
                maxlength:10,
            },
            Email:{
                required:true,
                email:true,
            },
            /*aadhaar:{
                required:true,
                digits:true,
                minlength:16,
                maxlength:16,
            },
            business:{
                required:true,
                maxlength:200,
            },
            PAN:{
                required:true,
                minlength:10,
                maxlength:10,
            },
            acno:{
                required:true,
                digits:true,
                minlength:10,
                maxlength:18,
            },
            IFSCCode:{
                required:true,
                minlength:5,
                maxlength:18,
            },
            businessactivity:{
                required:true,
                maxlength:200,
            }*/
        },
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
        submitHandler:function(form){
            let formdata = $(form).serializeArray();
            //now save all the form data into the storage 
            let uar_form_key_value={};
            $.each(formdata,function(i,item){
                uar_form_key_value[item.name] = item.value;
            });
            cleareData('uar_form_key_value');
            //how send send email about this complain
            let path = "backend.php?func=uar_basic_info";
            doAjaxCall(path,uar_form_key_value,function(response){
                console.log(response);
                if(!response.error){
                    uar_form_key_value['registration_id']=response.full_res.registration_id;
                    doSaveData('uar_form_key_value',uar_form_key_value);
                    form.submit();
                }
            });
        }
    });
    // rti payment section 
    $("#msme_payment").bind("click",function(e){
        e.preventDefault();
        let uar_form_key_value = getSavedData('uar_form_key_value');
        //now call for pyment response 
        let path = "backend.php?func=msme_payment_request";
        doAjaxCall(path,uar_form_key_value,function(response){
            if(!response.error){
                //need to redirect to the payment url
                window.location=response.full_res.url;//get the payment url redirection
            }
        });
    });
    //END :: REGISTRATION AND FILLING 

    //RTI SECTION
    //rti form section 
    $("#rti_form1").validate({
        rules:{
            name:{
                required:true,
                maxlength:100,
            },
            fname:{
                //required:true,
                maxlength:200,
            },
            mno:{
                required:true,
                digits:true,
                minlength:10,
                maxlength:10,
            },
            Email:{
                required:true,
                email:true,
            },
            address:{
                //required:true,
                maxlength:300,
            },
            states:{
                //required:true,
                maxlength:150,
            },
            pin:{
                //required:true,
                minlength:6,
                maxlength:6,
            },
            dept_name:{
                //required:true,
                maxlength:100,
            },
            topic:{
                //required:true,
                maxlength:200,
            },
        },
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
        submitHandler:function(form){
            // maild basic details of the user who filled the data
            let frmdata = $(form).serializeArray();
            //now save all the form data into the storage 
            let rti_form_key_value={};
            $.each(frmdata,function(i,item){
                rti_form_key_value[item.name] = item.value;
            });
            cleareData('rti_form_key_value');
            
            //how send send email about this complain
            let path = "backend.php?func=rti_basic_info";
            doAjaxCall(path,rti_form_key_value,function(response){
                if(!response.error){
                    rti_form_key_value['registration_id']=response.full_res.registration_id;
                    doSaveData('rti_form_key_value',rti_form_key_value);
                    form.submit();
                }
            });
        }
    });

    // rti payment section 
    $("#rti_payment").bind("click",function(e){
        e.preventDefault();
        let rti_form_key_value = getSavedData('rti_form_key_value');
        //now add the extra value is urgent or not 
        let urgent_work=0;
        if($("#urgent_work").is(":checked")){
            urgent_work=1;
        }
        rti_form_key_value['urgent_work']=urgent_work;
        cleareData('rti_form_key_value');
        doSaveData('rti_form_key_value',rti_form_key_value);
        //now call for pyment response 
        let path = "backend.php?func=rti_payment_request";
        doAjaxCall(path,rti_form_key_value,function(response){
            if(!response.error){
                //need to redirect to the payment url
                window.location=response.full_res.url;//get the payment url redirection
            }
        });
    });
    //END RTI 
    
    //FEEDBACK SECTION
    $("#feedback").validate({
        rules:{
            name:{
                required:true,
                maxlength:100,
            },
            mobileno:{
                required:true,
                digits:true,
                minlength:10,
                maxlength:10,
            },
            email:{
                required:true,
                email:true,
            },
            subject:{
                required:true,
                maxlength:200,
            },
            message:{
                required:true,
                maxlength:200,
            },
        },
        errorElement:"em",
        errorClass:"text-danger",
        validCalss:"text-success",
        submitHandler:function(form){
            // maild basic details of the user who filled the form
            let frmdata = $(form).serializeArray();
            let feedback_form_key_value={};
            $.each(frmdata,function(i,item){
                feedback_form_key_value[item.name] = item.value;
            });
            let path = "backend.php?func=feedback";
            doAjaxCall(path,feedback_form_key_value,function(response){
                if(!response.error){
                    form.reset();
                }
                alert(response.msg);
            });
        }
    });
    // END FEEDBACK
});

//basepath retrive
function getBasePath(){
    let htef = location.href;
    baseUrl= htef.substring(0,htef.lastIndexOf('/')+1);
}
//validator function

function doAjaxCall(path,data,callback){
    let msg='Something wrong.';
    let error=true;
    let full_res={};
    $.ajax({
        url:baseUrl+path,
        type:'post',
        dataType:'json',
        contentType:'application/json;',
        data:JSON.stringify(data),
        start:function(){
            console.log("start");
        },
        success:function(response){
            msg=response.messages;
            if(response.status){
                error=false;
            }
            full_res=response;
        },
        error:function(){
            console.log("error");
        },
        complete:function(){
            console.log("End");
            callback({error:error,msg:msg,full_res:full_res});
        }
    });
}

//store the data into the browser db
function doSaveData(keyName='',datas=[]){
    if(checkedSupport()){
        window.localStorage.setItem(keyName,JSON.stringify(datas));
    }
}

function getSavedData(keyName=''){
    let dataObj=[];
    if(checkedSupport()){
        let datastr = window.localStorage.getItem(keyName);
        dataObj =  JSON.parse(datastr);
    }
    return dataObj;
}

function cleareData(keyName){
    if(checkedSupport()){
        window.localStorage.removeItem(keyName);
    }
}

function checkedSupport(){
    if (typeof(Storage) !== "undefined") {
        return true;
    } else {
        // No web storage Support.
        console.log("browser not supported ");
        return false;
    }
}

//talk.to chat widget 
function talk_io_widget(){
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/5e58eab6298c395d1cea4474/default';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
    })();
}
//end talk widget