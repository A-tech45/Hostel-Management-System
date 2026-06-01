// Combining two arrays

let fruits1 = [ "apple" , "mango" , "banana" ] ;

let fruits2 = [ "papaya" , "grapes" , "kiwi" ];

const fruits = fruits1.concat(fruits2);     //it combines fruits1 and fruits2

console.log(fruits) ;



fruits1.push(fruits2) ;        //adds fruits 2 inside fruits1   E.g = [fruits1 [fruits2]]
console.log(fruits1)   ;
console.log(fruits1[3][1])  ;  // to access the first element of the 3rd element