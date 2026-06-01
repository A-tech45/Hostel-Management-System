//data types in js
   // ---  >> 7 primitive data typesd
   //  string , number , Boolean , null , nudefined , symbol , BigInt 
    // Referance (NON PRIMITIVE)
    //array , objects , function


    const score = 100  // number data type

    const value = 200.09  // float special mentetion not required

    const isloggedin = true  // boolean data type

    const outside = null  // empty
    
    console.log(typeof score)

    const id = Symbol("43")   // 43  will be saved as symbol

    const bignum = 5454323523n // n is used to represent big int

    const chindis = ["Manoj" , "Ritesh" , "sushil"]  // array data types      return type object

       console.log(typeof chindis)

    let op = {

        name : " hello",     // object data types  return type is object
        age : 32 ,
    }
    
    console.log(op)  //display the object 
    chindis  // displays the return type of the data  


const myfunction = function(){
    console.log("hello world") 
}

console.log(typeof myfunction)    // return type function 