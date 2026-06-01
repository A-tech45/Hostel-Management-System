//      switch (key){
//      case value :
//        break ;         //  This is the  synatax of switch case

//     default:
//        break ;

//}



const num = 2

switch(num){

  case 1 :                    //  Case value  -->> Here 1 is the which will be compared with num and executes the result acc to the condition
  console.log("Hello") ;      //  Here it checks if the vaule is same as num if true it executes the result
  break;

  case 2 :
    console.log("Hello again");
    break ;

    default:
        console.log("Pello");              //  Default is the value which will be executed if it does not meet the conditions
        break;                             // as here 3 is not in the case so default will be executed  


}



//let arr = [] 
 
///if (arr.length === 0){              -->>checks if the array is empty 
//    console.log("Hello")
//}

// simialarly we can checks objects 

//const emptyObj = {}

//if(Object.keys(emptyObj).length === 0){             //  checks if the object is empty 
//    console.log("objecy is empty")
//}




//  Null Coalescing Operator ( ?? ) : null , undefined

//val1 = null ?? 10   // this operator checks if it is returning null/Undefined or not if null then ignores it ang gives another value 
val1 = null ?? 10 ?? 30    // in this case the first value after null will be printed  
console.log(val1)


// TERTINARY OPERATOR  ( ? : )

const price = 10    // this statement is a shortcut of if else here  the Syntax is :-
                     // condition ? Statement to be executed if true : statement to be executed while false

    price >= 20 ? console.log("costly") : console.log("chep")
//        ^                 ^                  ^
// This is the condition  |  Statement for true  | Statement for false