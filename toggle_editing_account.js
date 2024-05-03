function toggleEditing(updateButtonId, updateElementsClass, submitButtonId, cancelButtonId, confirmCheckboxId){

    //Enables form editing
    document.getElementById(updateButtonId).addEventListener('click', function() {
        const toggleInputs = document.getElementsByClassName(updateElementsClass);
        Array.from(toggleInputs).forEach(function(toggleInput) {
            toggleInput.disabled = !toggleInput.disabled;
        });
        document.getElementById(updateButtonId).style.display = 'none';
        document.getElementById(submitButtonId).style.display = 'inline';
        document.getElementById(cancelButtonId).style.display = 'inline';
    });

    //Disables form editing
    document.getElementById(cancelButtonId).addEventListener('click', function() {
        const toggleInputs = document.getElementsByClassName(updateElementsClass);
        Array.from(toggleInputs).forEach(function(toggleInput) {
            toggleInput.disabled = !toggleInput.disabled;
        });
        document.getElementById(updateButtonId).style.display = 'inline';
        document.getElementById(submitButtonId).style.display = 'none';
        document.getElementById(cancelButtonId).style.display = 'none';
        document.getElementById(confirmCheckboxId).checked = false;
    });
}