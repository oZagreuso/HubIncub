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

document.querySelectorAll('[data-password-tools]').forEach((container) => {
    const passwordInput = container.querySelector('[data-password-input]');
    const confirmationInput = document.querySelector('[data-password-confirm]');
    const generateButton = container.querySelector('[data-password-generate]');
    const toggleButton = container.querySelector('[data-password-toggle]');
    const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%&*-_+=?';

    const randomCharacter = (characters) => {
        const values = new Uint32Array(1);
        window.crypto.getRandomValues(values);

        return characters[values[0] % characters.length];
    };

    const shuffle = (characters) => {
        const values = new Uint32Array(characters.length);
        window.crypto.getRandomValues(values);

        return characters
            .map((character, index) => ({ character, value: values[index] }))
            .sort((left, right) => left.value - right.value)
            .map((item) => item.character)
            .join('');
    };

    generateButton?.addEventListener('click', () => {
        const requiredCharacters = [
            randomCharacter('ABCDEFGHJKLMNPQRSTUVWXYZ'),
            randomCharacter('abcdefghijkmnopqrstuvwxyz'),
            randomCharacter('23456789'),
            randomCharacter('!@#$%&*-_+=?'),
        ];
        const remainingCharacters = Array.from({ length: 12 }, () => randomCharacter(alphabet));
        const password = shuffle([...requiredCharacters, ...remainingCharacters]);

        passwordInput.value = password;
        confirmationInput.value = password;
        passwordInput.type = 'text';
        confirmationInput.type = 'text';
        toggleButton.textContent = 'Masquer';
    });

    toggleButton?.addEventListener('click', () => {
        const nextType = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = nextType;
        confirmationInput.type = nextType;
        toggleButton.textContent = nextType === 'password' ? 'Voir' : 'Masquer';
    });
});
