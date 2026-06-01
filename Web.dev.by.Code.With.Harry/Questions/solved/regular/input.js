
let count = 0
let sum = 0
const readline = require("readline");

const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout
});

rl.question("Enter your name: ", (num) => {
        
  while(num>0){  
    
    temp = num % 10
    count++
    sum = sum + temp**count
    temp = Math.floor(temp/10)
            }

     if(sum === num){
      console.log("armstrong no")
     }
     else{
      console.log("itsnot")
     }



console.log(count)
  rl.close();


});