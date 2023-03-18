/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/structure/semestre_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 05/02/2023 13:01
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['detail']

  async detail(event) {
    if (event.target.dataset.state === 'open') {
      document.getElementById(`detail_semestre_${event.params.semestre}`).classList.add('d-none')
      this.detailTarget.innerHTML = ''
      event.target.dataset.state = 'close'
      event.target.firstElementChild.classList.add('fa-caret-right')
      event.target.firstElementChild.classList.remove('fa-caret-down')
    } else {
      const response = await fetch(event.params.url)
      this.detailTarget.innerHTML = await response.text()
      document.getElementById(`detail_semestre_${event.params.semestre}`).classList.remove('d-none')
      event.target.dataset.state = 'open'
      event.target.firstElementChild.classList.remove('fa-caret-right')
      event.target.firstElementChild.classList.add('fa-caret-down')
    }
  }

  async refreshListe(event) {
    const response = await fetch(event.params.url)
    this.detailTarget.innerHTML = await response.text()
  }
}
