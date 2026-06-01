//when we create objects from constructor its not singleton

// object literals
const mySym = Symbol("key1")
const user = {
    name: "akash",
    age: 12,
    "full-name": "akash",
    [mySym]: "hello",
}
console.log(user["name"])
console.log(user["age"]) // console.log(user.age)   Another way to access object
console.log(user["full-name"])
console.log(typeof [mySym])



// Change the value of objects
user.name = " billobadmosh"              //  This changes the value of name 
console.log(user["name"])
console.log(user)
//Object.freeze(user)   //  this freezes the object after this changes cannot be applied
//example
user.name = "billaman"
console.log(user.name)

// functions

user.greet = function () {
    console.log("hello");
}
user.greet()           // prints the object

// console.log(user)
user.greet2 = function () {
    console.log(`hello  , ${this.name} `)
}
user.greet2()







 