let rand = Math.random()
let first, second;
if (rand < 0.33) {
    first = "manoj"
}
else if (rand < 0.66 && rand >= 0.33) {
    first = "Ritesh"
}
else {
    first = " sushil"
}

if (rand < 0.33) {
    second = "borah"
       }
else if (rand < 0.66 && rand >= 0.33) {
    second = "chakma"
      }
else {
    second = " s chakma "
      }
  
        console.log(`${first} ${second}`)