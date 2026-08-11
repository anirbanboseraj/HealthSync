document.addEventListener("DOMContentLoaded", function () {

    const counters = document.querySelectorAll(".counter");

    counters.forEach(counter => {

        const target = parseInt(counter.getAttribute("data-target"));

        let current = 0;

        const duration = 1800;

        const increment = target / (duration / 20);

        function updateCounter() {

            current += increment;

            if (current < target) {

                counter.textContent = Math.floor(current);

                setTimeout(updateCounter, 20);

            } else {

                counter.textContent = target;

            }

        }

        updateCounter();

    });

});