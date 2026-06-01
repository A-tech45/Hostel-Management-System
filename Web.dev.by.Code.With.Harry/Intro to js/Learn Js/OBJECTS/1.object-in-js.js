

const mySym = Symbol("key1")

const user = {                       //  objects can be acessed by  object.name , etc
    
     name : "akash",
     "full" : "hello",               //full should be acessed through string(double quotes)
     [mySym] : "mykey1",              //symbol should be written in square brackets 
     email : "ak@gmail.com",
     age : 23 ,
     location : "dibrugarh"

}

console.log(user.name)
console.log(user.age)
console.log(user["email"])

console.log(user["full"])

console.log(typeof user[mySym])        // it return string as becuase mySym(key) is storing a string value "mykey1"
console.log(typeof mySym)            // it returns symbol  becuase it is declared as a symbol

user.name = "rilo"             //overwrites the  value of the name
console.log(user.name)

//Object.freeze(user)         //freezes the object and doesnot allows changes in their values
user.name = "Milo"

console.log(user)     //prints the object

user.greet = function(){         //adds function on the object
    console.log("hello")
}

user.greet2 = function(){
    console.log(`hello,${this.name}`)   // this adds or takes reference of the given object (Eg.name)
}
console.log(user.greet());                 //calls the function
console.log(user.greet2());