let update_sub = document.getElementById('update_sub');

update_sub.addEventListener('click', function() {
    let subStatus = document.getElementById('sub_status').value;

    if(document.getElementById('renew_sub').style.display === 'none' && document.getElementById('cancel_sub').style.display === 'none') {
        if(subStatus === 'Deactivated') {
            document.getElementById('renew_sub').style.display = 'block';
            document.getElementById('cancel_sub').style.display = 'none';
            document.getElementById('sub_checkbox').style.display = 'block';
        } else {
            document.getElementById('renew_sub').style.display = 'none';
            document.getElementById('cancel_sub').style.display = 'block';
            document.getElementById('sub_checkbox').style.display = 'block';
        } 
    } else {
        document.getElementById('renew_sub').style.display = 'none';
        document.getElementById('cancel_sub').style.display = 'none';
        document.getElementById('sub_checkbox').style.display = 'none';
        document.getElementById('confirmSubChange').checked = false;
    }
});