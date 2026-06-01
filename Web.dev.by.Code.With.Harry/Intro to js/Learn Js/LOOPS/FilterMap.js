let arr = [ "pillo" , "khilo" , "dhillo"]

const values = arr.forEach( (val) => {    
    //console.log(val)
    return val ;          //* For each loop doesnot returns values

})

//console.log(values)   //* It gives undefined


 //! FILTER

 let number = [ 1 , 2 , 3 , 4 , 5 , 6 , 7 , 8 , 9]

const val = number.filter( (num) => num > 2 )      //* The filter function returns values   (Which is saved in val)
                           //* here num >= 4 is the condition so the function returns the value according to the condition
//console.log(val)       //* and here val is printed

const valu = number.filter( (num) => {
       return num > 3          //* When there is parenthesis return keyword is required  becuase it creats a new scope 
})

//console.log(valu)

 //!    --->> Return value using logic    <<-----

const newnum = [] 

number.forEach( (num) => {
    if(num > 2){
        newnum.push(num)
    }
})
console.log(newnum)