function change_font_size() {
    // Get all elements with the class name 'font_size'
    const fontSizeElements = document.querySelectorAll('.font_size input');

    let selectedFontSize = null;

    // Loop through each element and check if it is checked
    fontSizeElements.forEach((element) => {
        if (element.checked) {
            // Get the ID of the checked element
            selectedFontSize = element.id;
        }
    });

    // If a font size is selected, change the size of all <p> tags
    if (selectedFontSize) {
        // Remove 'px' from the selected ID to obtain the numeric font size
        const fontSize = selectedFontSize.replace('px', '') + 'px';
        
        // Get all <p> tags on the whole webpage
        const paragraphs = document.querySelectorAll('p');
        
        // Loop through each <p> tag and change the font size
        paragraphs.forEach((p) => {
            p.style.fontSize = fontSize;
        });
    }
}