/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/structure/formation_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:32
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['detail', 'detailParcours']

  static values = { url: String }

  async detail(event) {
    if (event.target.dataset.state === 'open') {
      document.getElementById(`detail_formation_${event.params.formation}`).classList.add('d-none')
      this.detailTarget.innerHTML = ''
      event.target.dataset.state = 'close'
      event.target.firstElementChild.classList.add('fa-caret-right')
      event.target.firstElementChild.classList.remove('fa-caret-down')
    } else {
      const response = await fetch(event.params.url)
      this.detailTarget.innerHTML = await response.text()
      document.getElementById(`detail_formation_${event.params.formation}`).classList.remove('d-none')
      event.target.dataset.state = 'open'
      event.target.firstElementChild.classList.remove('fa-caret-right')
      event.target.firstElementChild.classList.add('fa-caret-down')
    }
  }

  async afficherParcours(event) {
    event.preventDefault()
    const idFormation = event.params.formation
    const isOpen = document.getElementById(`parcours_${idFormation}`) !== null

    if (!isOpen) {
      const zone = document.getElementById(`detailParcours_${idFormation}`)
      if (!zone) {
        return
      }

      zone.innerHTML = window.da.loaderStimulus
      const response = await fetch(`${this.urlValue}?formation=${idFormation}`)
      zone.innerHTML = await response.text()
      const clone = document.importNode(zone.content, true)
      zone.replaceWith(clone)
      this._updateToggleState(idFormation, true)
      return
    }

    const zone = document.getElementById(`parcours_${idFormation}`)
    if (!zone) {
      return
    }

    zone.innerHTML = ''
    const template = document.createElement('template')
    template.setAttribute('id', `detailParcours_${idFormation}`)
    zone.replaceWith(template)
    this._updateToggleState(idFormation, false)
  }

  _updateToggleState (idFormation, isOpen) {
    const toggles = document.querySelectorAll(`[data-formation-toggle-id="${idFormation}"]`)

    toggles.forEach((toggle) => {
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false')

      const label = toggle.querySelector('[data-formation-toggle-label]')
      if (label) {
        const openLabel = toggle.dataset.openLabel || 'Masquer les parcours'
        const closedLabel = toggle.dataset.closedLabel || 'Voir les parcours'
        label.textContent = isOpen ? openLabel : closedLabel
      }

      const iconWrappers = toggle.querySelectorAll('[data-formation-toggle-icon]')
      iconWrappers.forEach((iconWrapper) => {
        iconWrapper.classList.toggle('rotate-90', isOpen)
      })
    })
  }
}
