document.documentElement.classList.add('has-js');

const header = document.querySelector('[data-header]');

const updateHeaderState = () => {
    document.body.classList.toggle('is-scrolled', window.scrollY > 8);
};

if (header) {
    updateHeaderState();
    window.addEventListener('scroll', updateHeaderState, { passive: true });
}

document.querySelectorAll('[data-header]').forEach((siteHeader) => {
    const toggleButton = siteHeader.querySelector('[data-menu-toggle]');
    const navigation = siteHeader.querySelector('[data-main-nav]');

    if (!toggleButton || !navigation) {
        return;
    }

    const closeMenu = () => {
        siteHeader.classList.remove('is-menu-open');
        toggleButton.setAttribute('aria-expanded', 'false');
        toggleButton.querySelector('.menu-toggle-label').textContent = 'Ouvrir le menu';
    };

    const openMenu = () => {
        siteHeader.classList.add('is-menu-open');
        toggleButton.setAttribute('aria-expanded', 'true');
        toggleButton.querySelector('.menu-toggle-label').textContent = 'Fermer le menu';
    };

    toggleButton.addEventListener('click', () => {
        if (siteHeader.classList.contains('is-menu-open')) {
            closeMenu();
            return;
        }

        openMenu();
    });

    navigation.querySelectorAll('a').forEach((link) => {
        link.addEventListener('click', closeMenu);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenu();
        }
    });

    window.addEventListener('resize', () => {
        if (window.matchMedia('(min-width: 901px)').matches) {
            closeMenu();
        }
    }, { passive: true });
});

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

document.querySelectorAll('[data-member-directory]').forEach((directory) => {
    const cards = Array.from(document.querySelectorAll('[data-member-card]'));
    const departments = Array.from(directory.querySelectorAll('[data-member-area]'));
    const grid = document.querySelector('[data-member-card-grid]');
    const resetButton = directory.querySelector('[data-member-map-reset]');
    const status = directory.querySelector('[data-member-map-status]');
    const tooltip = directory.querySelector('[data-member-map-tooltip]');
    const departmentLabels = {
        '01': 'Ain',
        '02': 'Aisne',
        '03': 'Allier',
        '04': 'Alpes-de-Haute-Provence',
        '05': 'Hautes-Alpes',
        '06': 'Alpes-Maritimes',
        '07': 'Ardèche',
        '08': 'Ardennes',
        '09': 'Ariège',
        '10': 'Aube',
        '11': 'Aude',
        '12': 'Aveyron',
        '13': 'Bouches-du-Rhône',
        '14': 'Calvados',
        '15': 'Cantal',
        '16': 'Charente',
        '17': 'Charente-Maritime',
        '18': 'Cher',
        '19': 'Corrèze',
        '21': 'Côte-d’Or',
        '22': 'Côtes-d’Armor',
        '23': 'Creuse',
        '24': 'Dordogne',
        '25': 'Doubs',
        '26': 'Drôme',
        '27': 'Eure',
        '28': 'Eure-et-Loir',
        '29': 'Finistère',
        '2A': 'Corse-du-Sud',
        '2B': 'Haute-Corse',
        '30': 'Gard',
        '31': 'Haute-Garonne',
        '32': 'Gers',
        '33': 'Gironde',
        '34': 'Hérault',
        '35': 'Ille-et-Vilaine',
        '36': 'Indre',
        '37': 'Indre-et-Loire',
        '38': 'Isère',
        '39': 'Jura',
        '40': 'Landes',
        '41': 'Loir-et-Cher',
        '42': 'Loire',
        '43': 'Haute-Loire',
        '44': 'Loire-Atlantique',
        '45': 'Loiret',
        '46': 'Lot',
        '47': 'Lot-et-Garonne',
        '48': 'Lozère',
        '49': 'Maine-et-Loire',
        '50': 'Manche',
        '51': 'Marne',
        '52': 'Haute-Marne',
        '53': 'Mayenne',
        '54': 'Meurthe-et-Moselle',
        '55': 'Meuse',
        '56': 'Morbihan',
        '57': 'Moselle',
        '58': 'Nièvre',
        '59': 'Nord',
        '60': 'Oise',
        '61': 'Orne',
        '62': 'Pas-de-Calais',
        '63': 'Puy-de-Dôme',
        '64': 'Pyrénées-Atlantiques',
        '65': 'Hautes-Pyrénées',
        '66': 'Pyrénées-Orientales',
        '67': 'Bas-Rhin',
        '68': 'Haut-Rhin',
        '69': 'Rhône',
        '70': 'Haute-Saône',
        '71': 'Saône-et-Loire',
        '72': 'Sarthe',
        '73': 'Savoie',
        '74': 'Haute-Savoie',
        '75': 'Paris',
        '76': 'Seine-Maritime',
        '77': 'Seine-et-Marne',
        '78': 'Yvelines',
        '79': 'Deux-Sèvres',
        '80': 'Somme',
        '81': 'Tarn',
        '82': 'Tarn-et-Garonne',
        '83': 'Var',
        '84': 'Vaucluse',
        '85': 'Vendée',
        '86': 'Vienne',
        '87': 'Haute-Vienne',
        '88': 'Vosges',
        '89': 'Yonne',
        '90': 'Territoire de Belfort',
        '91': 'Essonne',
        '92': 'Hauts-de-Seine',
        '93': 'Seine-Saint-Denis',
        '94': 'Val-de-Marne',
        '95': 'Val-d’Oise',
        LU: 'Luxembourg',
    };

    const labelForArea = (area) => departmentLabels[area] || 'Zone sélectionnée';

    const moveTooltip = (event) => {
        if (!tooltip) {
            return;
        }

        const bounds = directory.getBoundingClientRect();
        tooltip.style.left = `${event.clientX - bounds.left}px`;
        tooltip.style.top = `${event.clientY - bounds.top}px`;
    };

    const showTooltip = (department, event = null) => {
        if (!tooltip || !department.classList.contains('is-active')) {
            return;
        }

        tooltip.textContent = labelForArea(department.dataset.memberArea);
        tooltip.hidden = false;

        if (event) {
            moveTooltip(event);
            return;
        }

        const directoryBounds = directory.getBoundingClientRect();
        const departmentBounds = department.getBoundingClientRect();
        tooltip.style.left = `${departmentBounds.left + departmentBounds.width / 2 - directoryBounds.left}px`;
        tooltip.style.top = `${departmentBounds.top + departmentBounds.height / 2 - directoryBounds.top}px`;
    };

    const hideTooltip = () => {
        if (tooltip) {
            tooltip.hidden = true;
        }
    };

    const setActiveArea = (area) => {
        cards.forEach((card) => {
            const isVisible = !area || card.dataset.memberArea === area;
            card.hidden = !isVisible;
            card.classList.toggle('is-map-filtered-out', !isVisible);
        });

        departments.forEach((department) => {
            department.classList.toggle('is-selected', department.dataset.memberArea === area);
        });

        if (status) {
            status.textContent = area
                ? `Membres localisés : ${labelForArea(area)}.`
                : 'Tous les membres localisés sont affichés.';
        }

        if (area && grid) {
            grid.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    departments.forEach((department) => {
        if (!department.classList.contains('is-active')) {
            return;
        }

        const selectDepartment = () => setActiveArea(department.dataset.memberArea);

        department.addEventListener('click', selectDepartment);
        department.addEventListener('mouseenter', (event) => showTooltip(department, event));
        department.addEventListener('mousemove', moveTooltip);
        department.addEventListener('mouseleave', hideTooltip);
        department.addEventListener('focus', () => showTooltip(department));
        department.addEventListener('blur', hideTooltip);
        department.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectDepartment();
            }
        });
    });

    resetButton?.addEventListener('click', () => setActiveArea(''));
});
