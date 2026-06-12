document.addEventListener('click', (e) => {
    const el = e.target.closest('[data-confirm]');
    if (!el) return;
    if (!confirm(el.dataset.confirm)) e.preventDefault();
}, true);
