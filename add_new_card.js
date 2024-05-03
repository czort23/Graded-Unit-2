//Toggles form editing
let add_new_card = document.getElementById('add_new_card');

add_new_card.addEventListener('click', function() {
    let toggleInput = document.querySelector('.new_card');
    if(toggleInput.style.display == 'inline') {
        toggleInput.style.display = 'none';
    } else {
        toggleInput.style.display = 'inline';
    }
});