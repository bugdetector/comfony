import { Controller } from '@hotwired/stimulus';

/*
* The following line makes this controller "lazy": it won't be downloaded until needed
* See https://symfony.com/bundles/StimulusBundle/current/index.html#lazy-stimulus-controllers
*/

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        'target-modal': String
    }

    connect() {
        console.log(this);
        this.element.addEventListener('click', () => {
            const modal = document.getElementById(this.targetModalValue);
            modal.showModal();
            for (let btn of modal.getElementsByClassName('modal-close')) {
                btn.addEventListener('click', () => {
                    modal.close();
                });
            }

        })
    }
}
