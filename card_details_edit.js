function showCardDetails(cardNo) {
    //Enables form editing
    let toggleInput = document.querySelector('.card_dets' + cardNo);
    let editButton = document.getElementById('edit_card' + cardNo);

    if(toggleInput.style.display == 'inline') {
        toggleInput.style.display = 'none';
        editButton.style.display = 'none';
    } else {
        toggleInput.style.display = 'inline';
        editButton.style.display = 'inline';
    }
}

function editCardDetails(cardNo) {
    const toggleInputs = document.getElementsByClassName('update_card' + cardNo);
    Array.from(toggleInputs).forEach(function(toggleInput) {
        toggleInput.disabled = !toggleInput.disabled;
    });
    if(document.getElementById('update_card' + cardNo).style.display == 'inline') {
        document.getElementById('update_card' + cardNo).style.display = 'none';
        document.getElementById('delete_card' + cardNo).style.display = 'none';
        document.getElementById('card_checkbox' + cardNo).style.display = 'none';
        document.getElementById('confirmCardUpdate' + cardNo).checked = false;
    } else {
        document.getElementById('update_card' + cardNo).style.display = 'inline';
        document.getElementById('delete_card' + cardNo).style.display = 'inline';
        document.getElementById('card_checkbox' + cardNo).style.display = 'block';
    }
}