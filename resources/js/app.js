const navigationToggle = document.querySelector('#mobile-navigation-toggle');
const navigation = document.querySelector('#primary-navigation');

if (navigationToggle && navigation) {
    navigationToggle.addEventListener('click', () => {
        const expanded = navigationToggle.getAttribute('aria-expanded') === 'true';

        navigationToggle.setAttribute('aria-expanded', String(!expanded));
        navigationToggle.textContent = expanded
            ? navigationToggle.dataset.openLabel
            : navigationToggle.dataset.closeLabel;
        navigation.classList.toggle('hidden', expanded);
    });
}

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (!(form instanceof HTMLFormElement) || form.dataset.confirmed === 'true') {
        return;
    }

    const deletesRecord = form.querySelector('input[name="_method"][value="DELETE"]') !== null;
    const message = form.dataset.confirm || (deletesRecord ? document.body.dataset.confirmDelete : null);

    if (message && !window.confirm(message)) {
        event.preventDefault();
    }
});
