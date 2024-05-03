let amount = document.getElementById('amount');
    let total_price = document.getElementById('total-price'); 

    amount.addEventListener("change", function() {
        total_price.value = amount.value * 10;
});