document.getElementsByClassName('hello')   // This returns a html collection

HTMLCollection(3) [li.hello, li.hello, li.hello]    // This is the html collection 

const op = Array.from(document.getElementsByClassName('hello'))   // we cant loop html collection so we converted it into an array using
                                                                 // Array.from() function 

op

(3) [li.hello, li.hello, li.hello]   // This is the converted array

op.forEach( function(val) {        // so now we can loop it 
    console.log(val)              // val gives the structure whose class name is hello
})

   //*     <li class="hello">one</li>   // so this is the value returned by the loop
   //*    <li class="hello">two</li>
   //*   <li class="hello">three</li>

   op.forEach( function(val) {
    console.log(val.textContent)           //-  .textContent gives the content of the structure like one in the  <li class="hello">one</li>
})
     
//*     one           --->> This is the value returned when applied text content in val
//*     two
//*     three