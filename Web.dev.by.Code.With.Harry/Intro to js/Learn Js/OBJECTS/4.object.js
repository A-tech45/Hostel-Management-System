// example of singleton object 

//const user2 = new object()    this creates a new singleton object

//const user = {}
//user.name = "Akash k"
//user.id = "213"
///console.log(user)


// combining objects
const obj1 = { 1: "a", 2: "b" }
const obj2 = { 3: "a", 4: "b" }

// const obj3 = { obj1 , obj2 }
// const obj3 = Object.assign({} , obj1 , obj2)         // used to combine the objects   the format is ( target , source )  -->> here {} is the target and obj1 and ibj2 are the sources

const obj3 = { ...obj1, ...obj2 };      // same method to merge objects 
//console.log(obj3)

const users = [            // In the array users we can store group of objects
    {
        id: 1,
        email:"op@gmail"
    },
    {
        id:2 ,
        email: " op2@gmail"
    }

]
//console.log(users[0].email)   // this prints the value of email that is inside an object at the 0 pos(index) of the array users
console.log(obj1);
console.log(Object.keys(obj1));               //Its return type is array  and it prints the keys of the object mentetioned
console.log(Object.values(obj1));             // This returns the values of the object mentetioned
console.log(Object.entries(obj1));              // here each key values in turned into arrays
console.log(obj1.hasOwnProperty('2')); 

const course = {
coursename : "js in hindi",
price : "999",                                   //    ----->>  THis is a object name course instructor 
courseinstructor : "Akash"
}

const {courseinstructor} = course              //  here the onject is being de structured 
const {courseinstructor: instructor} = course      //this is also same but here the course instructor is names as instructor

console.log(instructor);           // and here the instructor is displayed
console.log(courseinstructor);                // and printed