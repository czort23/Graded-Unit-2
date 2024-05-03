let total = document.getElementById('total'); 
let hidden_total = document.getElementById('hidden_total'); 
let greenPoints = document.getElementById('green_points'); 
let pointsCheck = document.getElementById('points_check'); 

function calculate() {
    let totalScore = 0;
    let selected = 0;
        

    elements.forEach(function(element, index) {
        switch(index % 3) {
            case 0:
                if (element.classList.contains("score")) {
                    selected++;
                }
                break;

            case 1:
                if (element.classList.contains("score")) {
                    totalScore += 5;
                    selected++;
                }
                break;

            case 2:
                if (element.classList.contains("score")) {
                    totalScore += 10;
                    selected++;
                }
                break;

            default:
                break;
        }
        if(selected != 10) {
            total.placeholder = 'You\'ve missed a couple.';
        } else if (pointsCheck.value != 0) {
            total.placeholder = 'You\'ve already used the calculator.';
        } else {
            total.value = totalScore;
            hidden_total.value = totalScore;
            greenPoints.disabled = false;
        }
    });
}   