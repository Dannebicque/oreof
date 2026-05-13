/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/oreofv2/assets/controllers/dropdown_menu_controller.js
 * @author davidannebicque
 * @project oreofv2
 * @lastUpdate 09/05/2026 15:59
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['trigger', 'menu']
  static values = {
    closeOnEscape: { type: Boolean, default: true },
    closeOnClickOutside: { type: Boolean, default: true }
  }

  connect () {
    this.closeMenuBound = this.closeMenu.bind(this)
    this.handleEscapeBound = this.handleEscape.bind(this)
  }

  disconnect () {
    document.removeEventListener('click', this.closeMenuBound)
    document.removeEventListener('keydown', this.handleEscapeBound)
  }

  toggle (event) {
    event.preventDefault()
    event.stopPropagation()

    const isHidden = this.menuTarget.classList.contains('hidden')

    if (isHidden) {
      this.open()
    } else {
      this.close()
    }
  }

  open () {
    this.menuTarget.classList.remove('hidden', 'opacity-0', 'invisible')
    // Trigger reflow to animate
    void this.menuTarget.offsetWidth
    this.menuTarget.classList.add('opacity-100', 'visible')

    document.addEventListener('click', this.closeMenuBound)
    if (this.closeOnEscapeValue) {
      document.addEventListener('keydown', this.handleEscapeBound)
    }
  }

  close () {
    this.menuTarget.classList.remove('opacity-100', 'visible')
    this.menuTarget.classList.add('opacity-0', 'invisible')

    setTimeout(() => {
      if (this.menuTarget.classList.contains('invisible')) {
        this.menuTarget.classList.add('hidden')
      }
    }, 150)

    document.removeEventListener('click', this.closeMenuBound)
    document.removeEventListener('keydown', this.handleEscapeBound)
  }

  closeMenu (event) {
    if (!this.element.contains(event.target)) {
      this.close()
    }
  }

  handleEscape (event) {
    if (event.key === 'Escape') {
      this.close()
      this.triggerTarget.focus()
    }
  }
}


