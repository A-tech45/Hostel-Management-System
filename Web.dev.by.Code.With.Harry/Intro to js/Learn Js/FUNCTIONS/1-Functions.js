// function lok(hlo) {
//     console.log("hello " +  hlo + " gud mrng ")  
//     console.log("hello " +  hlo + " r u nice ")  
//     console.log("hello " +  hlo + " it seems u are well ")     //<--- FUNCTIONS
// }
// lok("lope")   // <-- If the value of lok(lope) changes then the value of upper function lok(hlo) changes
//              //  indirectly lope = hlo
// lok("manoj")   //<--here the first value will be printed first then the another mean first lope will be printed then manoj


function sum(a,b) {
    console.log(a+b)    //<<--    This will print the sum of argument given in the sum function

}                   //<<----But this function will not return  a value the below function will return
 sum( 2 , 3)     //<<----   The sum of these arguments will be printed
  
 
 function add( x , y) {                                                        
    return x + y     //<<-- here the functin will return a value          
  } 
  result = add( 2 , 1)          //<<----  The returned valuewill be saved on result
  result1 = add( 3 , 1)          //<<----  The returned valuewill be saved on result
  result2 = add( 6 , 1)          //<<----  The returned valuewill be saved on result
  result3 = add( 2 , 9)          //<<----  The returned valuewill be saved on result
  
  console.log("The sum of these no is : ", result)  //<<---- Here thr result will be displayed   
  console.log("The sum of these no is : ", result1)  //<<---- Here thr result will be displayed   
  console.log("The sum of these no is : ", result2)  //<<---- Here thr result will be displayed   
  console.log("The sum of these no is : ", result3)  //<<---- Here thr result will be displayed   



  const func1 = (x)=> {
    console.log("hello " , x)    //<<---- So this line will print 4
  }
                                                          //<<This function is called arrow function   

  func1(4);   //<<--- Here the 4 will be the value of x