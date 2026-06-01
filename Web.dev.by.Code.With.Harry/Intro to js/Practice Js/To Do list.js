
const readline = require("readline/promises");

const rl = readline.createInterface({
  input: process.stdin,
  output: process.stdout
});

async function main() {
  
  let todo = [];
  console.log("This is a simple TO-DO ");
  
  while(true){

       console.log("  \n 1.Add ToDo  \n 2.Remove ToDo \n 3.View To Do \n 4.Exit");
       const option = await rl.question("Enter ur choice : \n") ;
       
       switch(option){
  case "1" :                    
      const add = await rl.question(" ADD :") ;
      todo.push(add) ;
  break;

  case "2" :
      const remove = await rl.question(" REMOVE NO :") ;
      const index = Number(remove);
      todo.splice(index-1 , 1);
  break ;

  case "3" :
    todo.forEach((t , i) => {
      console.log(`${i+1}. ${t}`);
    });
  break ;

  case "4" :
      console.log("Exiting.....")
      rl.close();
      return;
  default:
      console.log("Invalid input ")
  }
 }
}
main();