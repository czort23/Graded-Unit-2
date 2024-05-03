const search_bar = document.getElementById('search-bar');
const item_list = document.getElementById('item-list').getElementsByTagName('li');

search_bar.addEventListener('keyup', function(event) {
            
    const search_text = event.target.value.toLowerCase();
    Array.from(item_list).forEach(function(item) {
        const text = item.textContent.toLowerCase();
        if (text.includes(search_text)) {
            item.style.display = 'block';
        } else {
            item.style.display = 'none';
        }
    });
});

//Event listener to hide item list when clicking away from search bar
document.body.addEventListener('click', function(event) {
    if (event.target === search_bar) {
        document.getElementById('item-list').style.display = 'block';
    } else {
        document.getElementById('item-list').style.display = 'none';
    }
});