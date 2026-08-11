// =========================
// HealthSync Main JS
// =========================

console.log("HealthSync 2.0 Loaded Successfully");

// Smooth Scroll

document.querySelectorAll('a[href^="#"]').forEach(anchor=>{

    anchor.addEventListener("click",function(e){

        e.preventDefault();

        document.querySelector(this.getAttribute("href"))

        ?.scrollIntoView({

            behavior:"smooth"

        });

    });

});