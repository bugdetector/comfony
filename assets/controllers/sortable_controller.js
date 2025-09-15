import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import Sortable from 'sortablejs';
/* stimulusFetch: 'lazy' */
export default class extends Controller {

    component = null;
    sortable = null;

    connect() {
        getComponent(this.element.parentElement).then(component => this.component = component);
        this.sortable = Sortable.create(this.element, {
            filter: 'sortable-item',
            onEnd: (event) => {
                this.component.action(
                    'saveOrder', {
                        itemId: event.item.dataset.objectId,
                        newPosition: event.newIndex
                    }
                );
            },
        });
    }

    disconnect() {
        this.sortable.destroy();
    }
}
