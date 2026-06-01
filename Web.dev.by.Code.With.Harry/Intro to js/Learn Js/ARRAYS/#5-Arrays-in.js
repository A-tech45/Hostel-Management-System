// let arr = [ 2 ,4 ,8 , 10]
// arr[0] = 23;

// console.log(arr[0])
// console.log(arr[1])
// console.log(arr.toString())
let y = [2, 3, 4]

         y.pop()    ;          //pops  an element out of  an array

         y.push(2) ;            //push an element in the array 

         y.unshift(9)  ;        //adds the value in first place

         y.shift() ;            //remove the value added from shift

         console.log(y.includes(3)) ;  //checks the given element is present in the array or not ( Returns boolean value)

         console.log(y.includes(5)) ; //since 5 is not in the array so the value is false

         const newy = y.join()      // converts the array into string 

    console.log(newy);

         console.log(typeof(newy));    //this prints the type of datatype of the element

console.log(y)
// console.log(y.pop())
// console.log(y);
// console.log(y.push(43))
// console.log(y.push(4))
// console.log(y)
// console.log(y.shift())
// console.log(y)







{
    let x = [2, 4, 3];
    let newAr = []
    for (let i = 0; i < x.length; i++) {
        const element = x[i];
        newAr.push(element + 3)       //here in the place of + there can be any operator
    }

    console.log(newAr)
}