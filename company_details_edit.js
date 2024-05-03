//Enables/disables company details editing
let update_account = document.getElementById('update_account_btn');

update_account.addEventListener('click', function() {
    const toggleInputs = document.getElementsByClassName('updateAccount');
    Array.from(toggleInputs).forEach(function(toggleInput) {
        toggleInput.disabled = !toggleInput.disabled;

    });
    if(document.getElementById('update_account_info').style.display == 'inline') {
        document.getElementById('update_account_info').style.display = 'none';
        document.getElementById('account_checkbox').style.display = 'none';
        document.getElementById('confirm_account_update').checked = false;
    } else {
        document.getElementById('update_account_info').style.display = 'inline';
        document.getElementById('account_checkbox').style.display = 'block';
    }
});