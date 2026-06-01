const buton = document.querySelectorAll('.button')
const body = document.querySelector("body")


buton.forEach( function(button){
    button.addEventListener('click', function(op){
                 if(op.target.id === 'red'){
                    body.style.backgroundColor = op.target.id
                 }
                  if(op.target.id === 'green'){
                    body.style.backgroundColor = op.target.id
                 }
                  if(op.target.id === 'grey'){
                    body.style.backgroundColor = op.target.id
                 }
                  if(op.target.id === 'yellow'){
                    body.style.backgroundColor = op.target.id
                 }
    })
})