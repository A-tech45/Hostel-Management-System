

    for(let j = 1 ; j < 6 ; j++){
        if(j == 2){
             console.log(`${j} detected `)
             break ;  
             
        }                                        // The break statement breaks the   condition and loop when the condition is filled      
        console.log(j)                           // It simply exits the loop and condition
           
        
    }




    for(let j = 1 ; j < 6 ; j++){
        if(j == 2){
             console.log(`${j} detected `)
             continue ;  
             
        }                                        // The continue statement ignores the   condition and continues loop when the condition is filled      
        console.log(j)                           //It doesnot exits the loop like break statement 
        
    }