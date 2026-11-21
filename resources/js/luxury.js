const appFrame = document.querySelector('.app-frame');
const menuToggle = document.querySelector('[data-menu-toggle]');
const menuClose = document.querySelector('[data-sidebar-close]');

if (appFrame && menuToggle) {
    const narrow = window.matchMedia('(max-width: 900px)');
    const updateMenu = () => {
        const open = narrow.matches
            ? appFrame.classList.contains('sidebar-open')
            : !appFrame.classList.contains('sidebar-collapsed');
        menuToggle.setAttribute('aria-expanded', String(open));
    };
    const closeDrawer = () => {
        if (!appFrame.classList.contains('sidebar-open')) return;
        appFrame.classList.remove('sidebar-open');
        updateMenu();
        menuToggle.focus();
    };

    menuToggle.addEventListener('click', () => {
        appFrame.classList.toggle(narrow.matches ? 'sidebar-open' : 'sidebar-collapsed');
        updateMenu();
        if (narrow.matches && appFrame.classList.contains('sidebar-open')) {
            document.querySelector('.side-nav a')?.focus();
        }
    });
    menuClose?.addEventListener('click', closeDrawer);
    narrow.addEventListener('change', () => {
        appFrame.classList.remove('sidebar-open');
        updateMenu();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeDrawer();
    });
    updateMenu();
}

const topbarMenus = document.querySelectorAll('.topbar-menu');
for (const menu of topbarMenus) {
    menu.addEventListener('toggle', () => {
        if (!menu.open) return;
        for (const other of topbarMenus) {
            if (other !== menu) other.open = false;
        }
    });
}
document.addEventListener('click', (event) => {
    for (const menu of topbarMenus) {
        if (!menu.contains(event.target)) menu.open = false;
    }
});
document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    for (const menu of topbarMenus) {
        if (!menu.open) continue;
        menu.open = false;
        menu.querySelector('summary')?.focus();
    }
});

const publicToggle = document.querySelector('[data-public-menu-toggle]');
const publicNav = document.querySelector('[data-public-nav]');
publicToggle?.addEventListener('click', () => {
    const open = publicNav?.classList.toggle('is-open') ?? false;
    publicToggle.setAttribute('aria-expanded', String(open));
});

document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.dataset.passwordTarget ?? '');
        if (!(input instanceof HTMLInputElement)) return;
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.classList.toggle('is-visible', visible);
        button.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
        button.setAttribute('aria-pressed', String(visible));
    });
});

document.querySelectorAll('[data-transaction-type]').forEach((select) => {
    const form = select.closest('form');
    const fields = form?.querySelector('[data-transfer-fields]');
    const target = form?.querySelector('[data-transfer-target]');
    const updateTransferField = () => {
        const visible = select.value === 'transfer';
        if (fields) fields.hidden = !visible;
        if (target instanceof HTMLSelectElement) {
            target.required = visible;
            if (!visible) target.value = '';
        }
    };
    select.addEventListener('change', updateTransferField);
    updateTransferField();
});

document.querySelectorAll('[data-table-filter]').forEach((input) => {
    const table = document.querySelector(input.dataset.tableFilter ?? '');
    const rows = table?.querySelectorAll('tbody tr');
    if (!rows) return;
    input.addEventListener('input', () => {
        const term = input.value.trim().toLocaleLowerCase();
        rows.forEach((row) => {
            row.hidden = !row.textContent?.toLocaleLowerCase().includes(term);
        });
    });
});

document.querySelectorAll('[data-confirm]').forEach((form) => {
    form.addEventListener('submit', (event) => {
        const message = form.getAttribute('data-confirm') ?? 'Are you sure?';
        if (!window.confirm(message)) event.preventDefault();
    });
});
