let arr = [ 2 , 300 , 4 , 50 , 90 , 3];
let smallest =  Infinity;
let seconds =  Infinity;

for(let i = 0 ; i < arr.length ; i++){
    if(arr[i] < smallest){                          
        seconds = smallest;
        smallest = arr[i];
    }
    else if(arr[i] < seconds && arr[i] !== smallest){          
        seconds = arr[i];
    }
}
console.log(seconds)