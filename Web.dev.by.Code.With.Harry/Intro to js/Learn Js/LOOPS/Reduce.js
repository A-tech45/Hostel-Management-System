const num = [ 1 , 2 , 3 , 4]
//const nums = num.reduce( function (acc , curval){
//            console.log(` acc : ${acc} and cuvl : ${curval}`)
//           return acc + curval
//} , 0)

//const nums = num.reduce( (acc , cuvl) => acc + cuvl)

//console.log(nums)

const course = [
    {
        name : " java",
        price : 3999
    },
    {
        name : " ds",
        price : 13999
    },
    {
        name : " py",
        price : 399
    },
]

const pay = course.reduce( (acc , item) => acc + item.price , 0)     // can be used to add prices of items in shopping

console.log(pay)