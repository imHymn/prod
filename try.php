<div>
    <input type="text" class="btn" value="1">
    <input type="text" class="btn" value="1">
    <input type="text" class="btn" value="1">
    <button class="submit">Click</button>
</div>
<script>

const submit = document.querySelector('.submit');

submit.addEventListener('click',(e)=>{
e.preventDefault();

const input = document.querySelectorAll('[class*="btn"]');
let dataVal = 0;
for(let el of input){
    dataVal += Number(el.value);
}
console.log(dataVal);

})


</script>
