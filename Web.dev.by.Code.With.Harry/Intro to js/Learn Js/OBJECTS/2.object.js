const tuser = {}

tuser.id = "32";

tuser.name = "kia"

console.log(tuser);

const obj2 = {                 //main object obj2
 
    name : "obj",
    email : "obj@gmail.com",

    ok : {                     //nested object declared ok 

      name2 : " obj2",
      email2 : "obj2@gmail.com",

    }
}

console.log(obj2.ok.name2)     //access inside nested object