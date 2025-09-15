import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import Sortable from 'sortablejs';
/* stimulusFetch: 'lazy' */
export default class extends Controller {

    component = null;
    sortables = [];

    connect() {
        getComponent(this.element.parentElement).then(component => this.component = component);
        const sortableElements = [
            this.element,
            ...this.element.querySelectorAll('.sortable-group').values(),
        ];
        for(let element of sortableElements){
            this.sortables.push(
                Sortable.create(element, {
                    filter: '.sortable-disabled',
                    draggable: '.sortable-item',
                    group: { name: 'sortable-group', pull: true, put: true },
                    handle: '.cursor-move',
                    swapThreshold: 0.65,
                    animation: false,
                    forceFallback: true,
                    fallbackOnBody: true,
                    onEnd: (event) => {
                        // Find parentId for tree structure
                        let parentId = null;
                        const parentLi = event.to.closest('li[data-object-id]');
                        if (parentLi) {
                            parentId = parentLi.dataset.objectId;
                        }
                        this.component.action(
                            'saveOrder', {
                                itemId: event.item.dataset.objectId,
                                newPosition: event.newIndex,
                                parentId,
                            }
                        );
                    },
                })
            );
        }
        
        console.log(this);
    }

    disconnect() {
        for(let sortable of this.sortables){
            sortable.destroy();
        }
    }
}
