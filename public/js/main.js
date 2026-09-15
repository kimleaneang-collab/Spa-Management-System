document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-password-toggle]');
    const password = document.querySelector('#password');

    if (toggle && password) {
        toggle.addEventListener('click', () => {
            const showing = password.type === 'text';
            password.type = showing ? 'password' : 'text';
            toggle.textContent = showing ? 'Show' : 'Hide';
            toggle.setAttribute(
                'aria-label',
                showing ? 'Show password' : 'Hide password'
            );
        });
    }

    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', (event) => {
            const username = document.querySelector('#username');
            const password = document.querySelector('#password');

            username?.setCustomValidity('');
            password?.setCustomValidity('');

            if (!username?.value.trim()) {
                username?.setCustomValidity('Username is required.');
            }

            if (!password?.value) {
                password?.setCustomValidity('Password is required.');
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
            }
        });
    }
});
