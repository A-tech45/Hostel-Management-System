const user = {
    name: " akash",

    welcomeMessage: function(){

         console.log(`${this.name} , hello`)
         console.log(this)
    }
}

//user.welcomeMessage()
//console.log(this)            // returns an empty object 


function yup(){

    let user = "akash "
    console.log(this.user)      // it gives undefines
}

//yup()


// Arrow Function 

const op = () => {          // This function is written like this (Syntax)

    let user = "akash "
    console.log(this.user)      // it also gives undefines
}

//op()

//const adto = (n1 , n2) => {
 //   return n1 + n2             
//}
 
//const adto = (n1 , n2) => n1 + n2             // This also returns the same value 

const adto = (n1 , n2) => 
 ( n1 + n2)                               // This also returns the same value 
                                          //  The only difference is that using curly braces will require return to return the value
                                           // But using parenthesis dosen't requires return keyword

console.log(adto(3  ,34))

