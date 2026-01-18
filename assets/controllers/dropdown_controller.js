/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/dropdown_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 17/01/2026 08:34
 */
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['menu', 'button']
  static values = { confirm: String }

  connect () {
    this.boundClose = this.closeOnClickOutside.bind(this)
    document.addEventListener('click', this.boundClose)
    document.addEventListener('keydown', this.onKeyDown)
  }

  disconnect () {
    document.removeEventListener('click', this.boundClose)
    document.removeEventListener('keydown', this.onKeyDown)
  }

  onKeyDown = (event) => {
    if (event.key === 'Escape') this.close()
  }

  toggle (event) {
    event.stopPropagation()
    const isHidden = this.menuTarget.classList.toggle('hidden')
    this.buttonTarget.setAttribute('aria-expanded', isHidden ? 'false' : 'true')
  }

  closeOnClickOutside (event) {
    if (!this.element.contains(event.target)) this.close()
  }

  close () {
    if (!this.menuTarget.classList.contains('hidden')) {
      this.menuTarget.classList.add('hidden')
      this.buttonTarget.setAttribute('aria-expanded', 'false')
    }
  }

  confirm (event) {
    const message = this.confirmValue || event?.params?.confirm || this.element.dataset.dropdownConfirmValue
    const msg = this.hasConfirmValue ? this.confirmValue : (this.element.dataset.dropdownConfirmValue || null)

    const text = event.currentTarget?.dataset?.dropdownConfirmValue || msg
    if (text && !window.confirm(text)) {
      event.preventDefault()
      event.stopPropagation()
      this.close()
      return
    }
    this.close()
  }
}

