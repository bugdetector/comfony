import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['input', 'toggle']

    connect() {
    }

    toggle(e) {
        e.preventDefault();
        e.stopPropagation();
        const input = this.inputTarget;
        input.type = input.type === 'password' ? 'text' : 'password';
        e.target.classList.toggle('swap-active');
    }
}
