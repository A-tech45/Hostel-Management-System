const nums = [ 1 , 2 , 3 , 4 , 5]

//const num = nums.map( (n) => n + 1 )
const num = nums
                .map( (n) => n * 10 )       //* This is called chaining here the value updated first is passed to the second
                .map( (n) => n + 1 )       //* So here first 1 * 10 = 10 then 10 + 1 = 11 (n*10 then n + 1)
                                          //* chaining can be done multiple times
                .filter((n) => n > 30)              //* Here filter can also be applied for simplifying values        
 

console.log(num)