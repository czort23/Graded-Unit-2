let items = document.querySelectorAll('.carousel .carousel-item');

items.forEach((el) => {
    const min_per_slide = 4;
    let next = el.nextElementSibling;
    for (var i=1; i<min_per_slide; i++) {
        if (!next) {
            // wrap carousel by using first child
            next = items[0];
        }
        let clone_child = next.cloneNode(true);
        el.appendChild(clone_child.children[0]);
        next = next.nextElementSibling;
    }
});