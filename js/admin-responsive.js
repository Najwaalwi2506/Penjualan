document.addEventListener('DOMContentLoaded', function () {
    const wrapper = document.querySelector('.wrapper');
    const sidebar = document.querySelector('.sidebar');
    const navbar = document.querySelector('.navbar');

    if (!wrapper || !sidebar || !navbar) {
        return;
    }

    if (navbar.querySelector('.navbar-toggle')) {
        return;
    }

    const toggleButton = document.createElement('button');
    toggleButton.type = 'button';
    toggleButton.className = 'navbar-toggle';
    toggleButton.setAttribute('aria-label', 'Buka menu');
    toggleButton.setAttribute('aria-expanded', 'false');
    toggleButton.innerHTML = '<span class="material-symbols-outlined">menu</span>';

    const closeButton = document.createElement('button');
    closeButton.type = 'button';
    closeButton.className = 'sidebar-close';
    closeButton.setAttribute('aria-label', 'Tutup menu');
    closeButton.innerHTML = '<span class="material-symbols-outlined">close</span>';

    const overlay = document.createElement('div');
    overlay.className = 'sidebar-overlay';
    document.body.appendChild(overlay);

    sidebar.prepend(closeButton);
    navbar.insertBefore(toggleButton, navbar.firstChild);

    const closeSidebar = function () {
        sidebar.classList.remove('open');
        overlay.classList.remove('show');
        document.body.classList.remove('sidebar-open');
        toggleButton.setAttribute('aria-expanded', 'false');
    };

    const openSidebar = function () {
        sidebar.classList.add('open');
        overlay.classList.add('show');
        document.body.classList.add('sidebar-open');
        toggleButton.setAttribute('aria-expanded', 'true');
    };

    const toggleSidebar = function () {
        if (sidebar.classList.contains('open')) {
            closeSidebar();
        } else {
            openSidebar();
        }
    };

    toggleButton.addEventListener('click', function (event) {
        event.preventDefault();
        toggleSidebar();
    });

    closeButton.addEventListener('click', function (event) {
        event.preventDefault();
        closeSidebar();
    });

    overlay.addEventListener('click', closeSidebar);

    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            if (window.innerWidth <= 992) {
                closeSidebar();
            }
        });
    });

    const applyResponsiveState = function () {
        if (window.innerWidth <= 992) {
            closeSidebar();
        } else {
            closeSidebar();
            sidebar.classList.remove('open');
        }
    };

    window.addEventListener('resize', applyResponsiveState);
    applyResponsiveState();
});
