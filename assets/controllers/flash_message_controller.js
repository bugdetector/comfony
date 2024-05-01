import { Controller } from "@hotwired/stimulus"
import Swal from "sweetalert2"

// Connects to data-controller="flashmessage"
export default class FlashMessageController extends Controller {
  async connect() {
    Swal.fire({
        showConfirmButton: false,
        ...this.element.dataset,
    });
    this.element.remove();
  }
}