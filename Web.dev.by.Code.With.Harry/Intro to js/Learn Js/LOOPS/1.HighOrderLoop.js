// FOR OF LOOP 

let arr = [ 2 , 3 , 5 , 6]

for (const element of arr) {
    //console.log(element)
}

const msg = "HELLO"
for( const st of msg){        // It can prints strings also
    //console.log(st)
}

//    ---->> MAPS
 const map = new Map();
 map.set('In' ,'INDIA' )                    // for in loop is not applied as map is not iterable
 map.set('USA' ,'America' )  
 map.set('Fr' ,'France' )  
 for(const[key , values] of map){         // For of  loop is used in maps

    //console.log(key , values)
 }
 
 //console.log(map)


 // ---->> FOR IN LOOP

const myObj = {
   name : "akash",
   roll : 43
}

for( const key in myObj){
    console.log(` ${key}`)
}