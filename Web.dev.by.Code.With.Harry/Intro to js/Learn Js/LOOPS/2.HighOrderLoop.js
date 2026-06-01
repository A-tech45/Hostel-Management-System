const coding = [ "hello" , "fox " , "cat"];

// Here the function automatically loops and gives the values


coding.forEach( function (ele){    // ele refers to the element of the array It can be of any name 
                                    // Since it is a callback function so it doesnot have names
//console.log(ele)

})

// USING ARROW FUNCTION

coding.forEach( (ele) => {
    //console.log(ele)
})


// PRINT ME FUNCTION

function printMe(item){              
    console.log(item)
}

coding.forEach(printMe)