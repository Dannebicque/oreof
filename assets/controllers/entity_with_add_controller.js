/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/entity_with_add_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 23/02/2023 16:43
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['zoneAdd']

  showAdd() {
    this.zoneAddTarget.classList.remove('d-none')
  }
}
