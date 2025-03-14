// a single line JavaScript comment
/*
 multiple line
 comment for JavaScript
*/


// ========================================

// SCRIPT FILE SET UP
"use strict" // forces to use newest standards - JavaScript has had many upgrades, improvements, prior code (legacy code)

document.addEventListener("DOMContentLoaded", ()=> {
  // (e) adds errror catch
  // validates the content before submitting
$("#form1").on('submit',function(e){
   // set up variables for controls, not their content
   const email1=$("#email1");
    
   email1.next().text("This field must be filled out.");

   e.preventDefault(); // prevent submission by default until called page is setup

}); // end of submit event

}); // end ofDOM listener close < easiest to get messed up due to contrast to rest of code
