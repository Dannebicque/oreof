/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/oreofv2/assets/controllers/navbar_dropdown_controller.js
 * @author davidannebicque
 * @project oreofv2
 * @lastUpdate 14/04/2026 09:33
 */

import { Controller } from '@hotwired/stimulus'

/**
 * Contrôleur simple pour dropdowns et megas menus dans la navbar
 */
export default class extends Controller {
  static targets = ['content']

  toggle () {
    this.contentTarget.classList.toggle('hidden')
  }

  close (event) {
    if (!this.element.contains(event.target)) {
      this.contentTarget.classList.add('hidden')
    }
  }

  // static targets = ['button', 'menu'];
  //
  // connect() {
  //   this.close();
  // }
  //
  // toggle(event) {
  //   event.preventDefault();
  //   event.stopPropagation();
  //
  //   if (this.isOpen()) {
  //     this.close();
  //     return;
  //   }
  //
  //   this.open();
  // }
  //
  // open() {
  //   this._closeSiblingMenus();
  //   this.menuTarget.classList.remove('hidden');
  //   this.menuTarget.classList.add('show');
  //   this.buttonTarget.setAttribute('aria-expanded', 'true');
  // }
  //
  // close() {
  //   this._closeDescendants();
  //   this.menuTarget.classList.add('hidden');
  //   this.menuTarget.classList.remove('show');
  //   this.buttonTarget.setAttribute('aria-expanded', 'false');
  // }
  //
  // closeOnOutside(event) {
  //   if (!this.element.contains(event.target)) {
  //     this.close();
  //   }
  // }
  //
  // closeOnEscape(event) {
  //   if (event.key === 'Escape') {
  //     this.close();
  //   }
  // }
  //
  // isOpen() {
  //   return !this.menuTarget.classList.contains('hidden');
  // }
  //
  // _closeSiblingMenus() {
  //   const parent = this.element.parentElement;
  //   if (!parent) {
  //     return;
  //   }
  //
  //   parent.querySelectorAll(':scope > [data-controller~="navbar-dropdown"]').forEach((sibling) => {
  //     if (sibling === this.element) {
  //       return;
  //     }
  //
  //     const siblingMenu = sibling.querySelector(':scope > [data-navbar-dropdown-target="menu"]');
  //     const siblingButton = sibling.querySelector(':scope > [data-navbar-dropdown-target="button"]');
  //
  //     if (siblingMenu) {
  //       siblingMenu.classList.add('hidden');
  //       siblingMenu.classList.remove('show');
  //     }
  //
  //     if (siblingButton) {
  //       siblingButton.setAttribute('aria-expanded', 'false');
  //     }
  //   });
  // }
  //
  // _closeDescendants() {
  //   this.menuTarget
  //     .querySelectorAll('[data-navbar-dropdown-target="menu"], [data-navbar-dropdown-target="button"]')
  //     .forEach((el) => {
  //       if (el.getAttribute('data-navbar-dropdown-target') === 'menu') {
  //         el.classList.add('hidden');
  //         el.classList.remove('show');
  //       }
  //
  //       if (el.getAttribute('data-navbar-dropdown-target') === 'button') {
  //         el.setAttribute('aria-expanded', 'false');
  //       }
  //     });
  // }
}

