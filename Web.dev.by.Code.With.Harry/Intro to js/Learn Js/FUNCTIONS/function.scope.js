function one(){
    const username = " akash"

    function two(){
        console.log(username)
    }
    two()        // here two is executed
}

one()     // Here funcyion one is called which contains function two
            // without calling one two cannot be executed and directly two cannot be called 


            // function hoisting
            
    
            console.log(addone(3))      // this funcion can be called before declaration but
            function addone(num){
                return num + 1
            }


            //but in this function case u cannot call it before declaration the syntax are different
            const addtwo = function(num){
                return num + 2
            }