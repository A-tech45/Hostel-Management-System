

// LOOPING THROUGH OBJECTS 



const coding  = [
  { name : "hello" ,
    roll : 25
} ,
{
    name : "billa",
    roll : 2
} ,
{
    name : "chomu" ,        
    roll: 3
}
]

coding.forEach( (item) => {      // Here item stores the value of object 
    console.log(`${item.name} : ${item.roll}`)  // so accessing keys or values we can access through items
})