function add(num1 , num2){
  console.log(num1 + num2)     // it doesnot return any value 
}


//add(2 , "3")     // here 3 is a string so 2 is also treated as a string so it prints 23
//add(2 , "a")       // same here also
//add( 2 , null)    // gere null is empty so the output is only 2

//const result = add(2 , 3)    // its undefined because the funnction doesnot returns any value 
//console.log(result)


function add(num1 , num2){
  //let result = num1 + num2    
  //return result           //  now it returns  result as a value

  return num1 + num2   // now the function directly returns value without other variable
}
const result = add (2 , 3)          
console.log(result)     // so the result is printed
console.log(add(2 ,3))      // using this the value is directly printed 



function login(username = "sam"){    // here if no user name is given the default value will be sam
     if(!username){
        console.log("please enter a username")
    }
   return `${username} just logged in`
}

//console.log(login()) // if empty it returns undefines 
console.log(login("akash"))       // and if user gives a value sam will be overwritten with users value


