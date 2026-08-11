window.addEventListener("scroll",()=>{

    const navbar=document.querySelector(".navbar");

    if(window.scrollY>80){

        navbar.style.background="rgba(7,17,31,.92)";

        navbar.style.boxShadow="0 10px 35px rgba(0,0,0,.4)";

    }

    else{

        navbar.style.background="rgba(17,24,39,.65)";

        navbar.style.boxShadow="none";

    }

});