import { Controller } from "@hotwired/stimulus"


// Connects to data-controller="table-collection"
export default class FormCollectionController extends Controller {
  static targets = ["collectionContainer"]

    static values = {
        index    : Number,
        prototype: String,
        containerTag: String,
    }

    addCollectionItem(event)
    {
        const item = document.createElement(this.containerTagValue || 'li');
        item.innerHTML = this.prototypeValue.replace(/__name__/g, this.indexValue)
                                            .replace(/__name_index__/g, this.indexValue + 1);
        item.classList.add('collection-item');
        this.collectionContainerTarget.appendChild(item);
        this.indexValue++;
        initializeComponents()
        this.element.dispatchEvent(new CustomEvent('form-collection:addCollectionItem', { bubbles: true }));
    }

    removeCollectionItem(event)
    {
      event.target.closest('.collection-item').remove();
      this.element.dispatchEvent(new CustomEvent('form-collection:removeCollectionItem', { bubbles: true }));
    }
}