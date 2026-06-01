let arr1 = [ 1 , 2 , 3 ]
let arr2 = [ 4 , 5 , 6]
let arr3 = []

for( let i = 0 ; i< arr1.length ; i++){
       arr3.push(arr1[i]);
}                                                 //  in js if u add two arrays using loop or anything it gets converted into string 
                                                   // so here i copied the values of the arrays to the third   
for( let i = 0 ; i< arr2.length ; i++){
       arr3.push(arr2[i]);
}

console.log(arr3)
console.log(typeof(arr3))




// Another method  using spread operator (...)
let ar1 = [ 1 , 2]
let ar2 = [ 3 , 4]
let ar3 = [...ar1,...ar2]
console.log(ar3)


// And using Another method called concatination
//i am using the previous arrays

let ar4 = ar1.concat(ar2);
console.log(ar4)                       // This two are the shortest and cleanest ways   to merge two arrays