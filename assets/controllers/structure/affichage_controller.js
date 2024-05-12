/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/structure/ue_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 12/03/2023 16:12
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  toggleEc() {
    document.querySelectorAll('.ec').forEach((ec) => {
      ec.classList.toggle('d-none')
    })
  }

  toggleVersioning() {
    document.querySelectorAll('.show_versioning').forEach((ec) => {
      ec.classList.toggle('d-none')
    })
    document.querySelectorAll('.show_current').forEach((ec) => {
      ec.classList.toggle('d-none')
    })
  }
}
