//Enables form editing
let change_password = document.getElementById('changePassword');

change_password.addEventListener('click', function() {
    const toggleInputs = document.getElementsByClassName('changePassword');
    Array.from(toggleInputs).forEach(function(toggleInput) {
        toggleInput.disabled = !toggleInput.disabled;
    });
    if(document.getElementById('update_password').style.display == 'inline') {
        document.getElementById('update_password').style.display = 'none';
        document.getElementById('passwordCheckbox').style.display = 'none';
        document.getElementById('confirmPasswordChange').checked = false;
    } else {
        document.getElementById('update_password').style.display = 'inline';
        document.getElementById('passwordCheckbox').style.display = 'block';
    }
});