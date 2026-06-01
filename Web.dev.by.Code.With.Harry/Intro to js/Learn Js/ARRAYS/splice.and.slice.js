// SPLICE CONCEPT

let arr = [ 2 , 4 , 5 ]

arr.splice( 0 , 0 , 20 , 30)     // The format is ( indexNo to start , no of elements to remove , elements to add )
console.log(arr)                 // So here the index is 0 so it inserts element from the first index - And the delete count is 0 so it dosnt delete elements -
                                 //  Now add the elements u want to add elements in the array 


// SLICE CONCEPT
//It is used to extrat a portion of an array 
//It doesnot changes the original value
// syntax 
arr.splice(start, end)      //  Start --->> index to start / end --->> index to end                           