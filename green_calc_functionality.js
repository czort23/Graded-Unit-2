const elements = Array.from(document.getElementsByTagName("td"));
    
elements.forEach(function(element, index) {
    element.addEventListener('click', function() {
        switch(index % 3) {
            case 0:
                const nextElements = [];
                for(let i = 1 + index; i < 3 + index; i++) {
                    nextElements.push(elements[i]);
                }
                nextElements.forEach(function(nextElement) {
                    nextElement.style.backgroundColor = "white";
                    nextElement.classList.remove("score");
                });

                element.style.backgroundColor = "red";
                element.classList.add("score");
                          
                break;
            case 1:
                const sideElements = [];
                for(let i = index - 1; i < index + 2; i++) {
                    sideElements.push(elements[i]);
                }
                sideElements.forEach(function(sideElement) {
                    sideElement.style.backgroundColor = "white";
                    sideElement.classList.remove("score");
                });

                element.style.backgroundColor = "orange";
                element.classList.add("score");

                break;
            case 2:
                const prevElements = [];
                for(let i = index - 1; i > index - 3; i--) {
                    prevElements.push(elements[i]);
                }
                prevElements.forEach(function(prevElement) {
                    prevElement.style.backgroundColor = "white";
                    prevElement.classList.remove("score");
                });

                element.style.backgroundColor = "green";
                element.classList.add("score");

                break;
            default:
                break;
        }
    });
});