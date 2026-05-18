import { Controller } from "@hotwired/stimulus";

/**
 * Help Drawer Controller
 * Gère l'ouverture/fermeture du drawer d'aide
 * Persiste à travers les navigations Turbo
 */
export default class extends Controller {
  static targets = ["fab", "drawer", "close", "backdrop"];

  connect() {
    this.drawer = document.getElementById("helpOffcanvas");
    this.backdrop = document.getElementById("helpBackdrop");
    this.fab = document.getElementById("helpFabToggle");
    this.closeBtn = document.getElementById("helpOffcanvasClose");
  }

  toggle(event) {
    event?.preventDefault();
    if (this.isOpen()) {
      this.close();
    } else {
      this.open();
    }
  }

  open(event) {
    event?.preventDefault();

    if (!this.drawer) return;

    this.drawer.classList.remove("translate-x-full");
    this.drawer.classList.add("translate-x-0");
    this.drawer.setAttribute("aria-hidden", "false");

    if (this.fab) {
      this.fab.setAttribute("aria-expanded", "true");
    }

    if (this.backdrop) {
      this.backdrop.classList.remove("hidden");
      this.backdrop.classList.add("block");
      this.backdrop.setAttribute("aria-hidden", "false");
    }
  }

  close(event) {
    event?.preventDefault();

    if (!this.drawer) return;

    this.drawer.classList.remove("translate-x-0");
    this.drawer.classList.add("translate-x-full");
    this.drawer.setAttribute("aria-hidden", "true");

    if (this.fab) {
      this.fab.setAttribute("aria-expanded", "false");
    }

    if (this.backdrop) {
      this.backdrop.classList.add("hidden");
      this.backdrop.classList.remove("block");
      this.backdrop.setAttribute("aria-hidden", "true");
    }
  }

  isOpen() {
    return this.drawer && !this.drawer.classList.contains("translate-x-full");
  }

  onBackdropClick(event) {
    event?.preventDefault();
    this.close();
  }

  onEscape(event) {
    if (event.key === "Escape" && this.isOpen()) {
      this.close();
    }
  }
}
