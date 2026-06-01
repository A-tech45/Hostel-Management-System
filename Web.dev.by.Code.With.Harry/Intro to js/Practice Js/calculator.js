let a = 10 ;
let b = 3 ;
const num = 1

switch(num){

  case 1 :                    //  Case value  -->> Here 1 is the which will be compared with num and executes the result acc to the condition
  sum = a + b ;
  console.log(sum);      //  Here it checks if the vaule is same as num if true it executes the result
  break;

  case 2 :
    mul = a * b;
    console.log(mul);
    break ;

    default:
        console.log("Pello");              //  Default is the value which will be executed if it does not meet the conditions
        break;                             // as here 3 is not in the case so default will be executed  

}