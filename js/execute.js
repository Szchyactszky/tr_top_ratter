jQuery(document).ready(function() {
//	console.log('i am anqued and working');
	
	
	
	
	// jquery date picker
//
//		jQuery("#datepicker3").datepicker();
//		jQuery( "#datepicker3" ).datepicker( "option", "dateFormat","yy-mm-dd" );
		
		//select from to date 
		 var dateFormat = "mm/dd/yy",
//		 var dateFormat = 'yy-mm-dd',
	      from = jQuery( "#T1" )
	        .datepicker({
	          defaultDate: "",
	          changeMonth: false,
	          numberOfMonths: 1,
	          showOtherMonths: true,
	          selectOtherMonths: true
	        })
	        .on( "change", function() {
	          to.datepicker( "option", "minDate", getDate( this ) );
	        }),
	      to = jQuery( "#T2" ).datepicker({
	        defaultDate: "+1d",
	        changeMonth: false,
	        numberOfMonths: 1,
	        showOtherMonths: true,
	        selectOtherMonths: true
	      })
	      .on( "change", function() {
	        from.datepicker( "option", "maxDate", getDate( this ) );
	      });
	 
	    function getDate( element ) {
	      var date;
	      try {
	        date = jQuery.datepicker.parseDate( dateFormat, element.value );
	      } catch( error ) {
	    	  console.log(error);
	        date = null;
	      }
	 
	      return date;
	    }

	
	
var format_isk_b = document.getElementById('format_isk');
function format_isk_function() {
    //alert('format_isk_cell!');
	

		 jQuery('.format_isk_cell').each(function(){
			 
		 
			 if (jQuery(this).hasClass('format_no_spaces')) {
				
				 var isk=jQuery(this).text();
				
				 var result = addSpaces(isk);
				 
				 jQuery(this).text(result);
				

			 }else{
				var isk=jQuery(this).text();
				//remove spaces from string.
				isk = isk.replace(/\s+/g, '');
				//return string back to its place.
				jQuery(this).text(isk);

			 }
	
		 jQuery(this).toggleClass('format_no_spaces');
	 });
}
format_isk_b.onclick = format_isk_function;

function addSpaces(nStr)
{
	nStr += '';
	x = nStr.split('.');
	x1 = x[0];
	x2 = x.length > 1 ? '.' + x[1] : '';
	var rgx = /(\d+)(\d{3})/;
	while (rgx.test(x1)) {
		x1 = x1.replace(rgx, '$1' + ' ' + '$2');
	}
	return x1 + x2;
}		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
		
	
	
	
});
	