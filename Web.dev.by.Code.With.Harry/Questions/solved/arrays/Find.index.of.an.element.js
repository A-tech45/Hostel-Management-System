let arr = [ 1 , 3 , 4 , 6]
let num = 3 ; 
pos = 0
index = 0

for( let i = 0 ; i< arr.length ; i++){
    
    if(arr[i] === num){
     pos = i + 1 ;                 // This finds the position of an element  ( pos - 1 ) becuase index starts from 0
     index = i ;                  // This finds the index of an element 
    }
}
console.log(pos)
console.log(index)