const display = document.getElementById('display');
let current = '';
let previous = null;
let operator = null;

function updateDisplay(v){ display.value = String(v); }

function clearAll(){ current=''; previous=null; operator=null; updateDisplay('0'); }

function backspace(){ if(current.length>0){ current = current.slice(0,-1); updateDisplay(current || '0'); } }

function applyPercent(){ if(current==='') return; const val = parseFloat(current); current = String(val/100); updateDisplay(current); }

function compute(){ if(previous === null || operator === null || current === '') return;
  const a = parseFloat(previous);
  const b = parseFloat(current);
  let res = 0;
  switch(operator){
    case '+': res = a + b; break;
    case '-': res = a - b; break;
    case '*': res = a * b; break;
    case '/': res = b === 0 ? 'Error' : a / b; break;
    default: return;
  }
  if(typeof res === 'number') res = +res.toPrecision(12);
  current = String(res);
  previous = null;
  operator = null;
  updateDisplay(current);
}

document.querySelectorAll('.btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const v = btn.dataset.value;
    if(!v) return;
    if(v === 'C') return clearAll();
    if(v === '←') return backspace();
    if(v === '%') return applyPercent();
    if(v === '=') return compute();
    if(['+','-','*','/'].includes(v)){
      if(current === '' && v === '-') { current = '-'; updateDisplay(current); return; }
      if(current === '') return;
      if(previous !== null){ compute(); }
      previous = current;
      operator = v;
      current = '';
      updateDisplay(operator);
      return;
    }
    // digit or dot
    if(v === '.' && current.includes('.')) return;
    current = (current === '0' && v !== '.') ? v : (current + v);
    updateDisplay(current);
  });
});

// keyboard support
window.addEventListener('keydown', (e) => {
  const key = e.key;
  if((/^[0-9]$/).test(key)) document.querySelector(`.btn[data-value="${key}"]`).click();
  if(key === '.') document.querySelector('.btn[data-value="."]').click();
  if(key === 'Enter' || key === '=') document.querySelector('.btn[data-value="="]').click();
  if(key === 'Backspace') document.querySelector('.btn[data-value="←"]').click();
  if(key === 'Escape') document.querySelector('.btn[data-value="C"]').click();
  if(['+','-','*','/'].includes(key)) document.querySelector(`.btn[data-value="${key}"]`)?.click();
});
