import './bootstrap';

import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import './spin-chart';

window.Alpine = Alpine;
window.Chart = Chart;

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) return;
    if (form.dataset.confirmed === 'true') {
        delete form.dataset.confirmed;
        return;
    }

    event.preventDefault();
    window.dispatchEvent(new CustomEvent('app-confirm', {
        detail: { form, message: form.dataset.confirm },
    }));
}, true);

Alpine.start();
