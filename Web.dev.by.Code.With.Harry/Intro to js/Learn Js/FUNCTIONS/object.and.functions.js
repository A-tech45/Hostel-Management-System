function calcprice(val1, val2 ,...num1){             // this ... is called rest / spread operator it combines the inputs into an array
    return num1
}                             //  now here the values except thr value1 and value2 will be inserted inside the array

console.log(calcprice(2130 , 3423 ,200 , 432))



const user = {
    name : "akash" ,
    price: 299
}

function check(anyobject){       //Here any object is given to handle objects not a particular one
    console.log(`username is ${anyobject.name} and price is ${anyobject.price}`)
}

check(user)             // Here the input will be only objects

check({
    name: " bikash",   // here u can also directly pass objects
    price: 499                    //and the values will be taken from here 
})


 //  Same we can do with arrays

 const arr1 = [ 2 , 4 , 6]

 function return1stvalue(getarray){    
    return getarray[2]           // this returns the second value of the arrays
 }
 //console.log(return1stvalue(arr1))

 //here we can also directly pass array values
 console.log(return1stvalue([200 , 300 , 400]))  