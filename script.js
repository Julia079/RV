document.addEventListener("DOMContentLoaded", function () {

    // --- 1. LOGIN/REGISTER PAGE LOGIC ---
    const signUpButton = document.getElementById('signUp');
    const signInButton = document.getElementById('signIn');
    const container = document.getElementById('container');

    // Only run this if we are actually on the Login page
    if (signUpButton && signInButton && container) {
        signUpButton.addEventListener('click', () => {
            container.classList.add("right-panel-active");
        });

        signInButton.addEventListener('click', () => {
            container.classList.remove("right-panel-active");
        });
    }

    // --- 2. DASHBOARD NAVBAR SCROLL EFFECT ---
    const header = document.querySelector('header');

    // Only run this if a header actually exists (Dashboard page)
    if (header) {
        window.addEventListener('scroll', function () {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

});