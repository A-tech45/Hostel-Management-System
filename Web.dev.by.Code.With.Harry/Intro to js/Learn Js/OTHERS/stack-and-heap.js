// Stack (primitive data types)   -->>  stackes stores the primitive data types

//heap(Non-Primitive)    -->>   heap stores the non-primitive data types


// STACK

let manoj = "ritesh"

let sushil = manoj

  sushil = "pankaj"                     //In stack the copy value is taken so the original value does not changes . 

console.log(sushil)
console.log(manoj)


//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++//


//   HEAP 

let userm ={      //<<-----this is an object .
    email : "op@gmail.com" ,
    name : "op" ,
}

let usern = userm                      // In case of heap the data is taken directly from the reference(original) value so when the value is changed the original value changes

usern.email = "hello@gmail.com"        // as i changed the value of usern the value in userm also changes

console.log(userm)
console.log(usern)