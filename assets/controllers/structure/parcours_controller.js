/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/structure/parcours_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:32
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['detail']

  async detail(event) {
    let response = await fetch(event.params.url)
    this.detailTarget.innerHTML = await response.text()
    document.getElementById(`detail_parcours_${event.params.parcours}`).classList.remove('d-none')

    if (event.target.dataset.state === 'open') {
      document.getElementById(`detail_parcours_${event.params.parcours}`).classList.add('d-none')
      this.detailTarget.innerHTML = ''
      event.target.dataset.state = 'close'
      event.target.firstElementChild.classList.add('fa-caret-right')
      event.target.firstElementChild.classList.remove('fa-caret-down')
    } else {
      response = await fetch(event.params.url)
      this.detailTarget.innerHTML = await response.text()
      document.getElementById(`detail_parcours_${event.params.parcours}`).classList.remove('d-none')
      event.target.dataset.state = 'open'
      event.target.firstElementChild.classList.remove('fa-caret-right')
      event.target.firstElementChild.classList.add('fa-caret-down')
    }
  }
}
