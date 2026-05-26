const header = document.querySelector('[data-header]');

const updateHeaderState = () => {
    document.body.classList.toggle('is-scrolled', window.scrollY > 8);
};

if (header) {
    updateHeaderState();
    window.addEventListener('scroll', updateHeaderState, { passive: true });
}

document.querySelectorAll('a[href^="#"]').forEach((link) => {
    link.addEventListener('click', (event) => {
        const target = document.querySelector(link.getAttribute('href'));

        if (!target) {
            return;
        }

        event.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
