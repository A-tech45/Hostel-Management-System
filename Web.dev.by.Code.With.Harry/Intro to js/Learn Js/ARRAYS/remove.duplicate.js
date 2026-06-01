let arr = [ 2 , 3  , 5 , 6 , 6 ,7]
let arr2 = []

for(let i = 0 ; i < arr.length ; i++){

    arr2.push(arr[i])
      if( arr2.includes(arr[i])){
        arr2.splice( i , 1)

      }
      else{
    
      }
    
}
console.log(arr2)