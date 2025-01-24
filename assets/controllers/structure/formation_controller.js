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
    const idFormation = event.params.formation
    const icone = event.target.firstElementChild.classList
    if (icone.contains('fa-caret-right')) {
      const zone = document.getElementById(`detailParcours_${idFormation}`)

      icone.remove('fa-caret-right')
      icone.add('fa-caret-down')

      zone.innerHTML = window.da.loaderStimulus
      const response = await fetch(`${this.urlValue}?formation=${idFormation}`)
      zone.innerHTML = await response.text()
      const clone = document.importNode(zone.content, true)
      zone.replaceWith(clone)
    } else {
      const zone = document.getElementById(`parcours_${idFormation}`)
      icone.remove('fa-caret-down')
      icone.add('fa-caret-right')
      zone.innerHTML = ''
      const template = document.createElement('template')
      template.setAttribute('id', `detailParcours_${idFormation}`)
      zone.replaceWith(template)
    }
  }
}
