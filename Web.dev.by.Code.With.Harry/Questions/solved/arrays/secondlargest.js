let arr = [2, 3, 4, 5, 85, 75, 6, 76]
let lrge = 0
let secondlrgst = 0
for (let i = 0; i < arr.length; i++) {
    if (arr[i] > lrge) {
        secondlrgst = lrge
        lrge = arr[i]
    }
    else if (arr[i] > secondlrgst && arr[i] < lrge) {
        secondlrgst = arr[i]
    }
}
console.log(secondlrgst)

