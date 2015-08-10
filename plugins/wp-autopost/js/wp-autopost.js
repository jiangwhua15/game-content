function showHTML(){
 var s=document.getElementById("ap_content_s").value;
  if(s==0){
    jQuery("#ap_content").hide();
	jQuery("#ap_content_html").show();
	document.getElementById("ap_content_s").value=1;
  }else{
	jQuery("#ap_content_html").hide();
	jQuery("#ap_content").show();
	document.getElementById("ap_content_s").value=0;  
  }
}

function Delete(id){
  if(confirm("Confirm Delete?")){ 
	 document.getElementById("saction").value='deleteSubmit';
	 document.getElementById("configId").value=id;
     document.getElementById("myform").submit();
  }else return false; 
}