/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/tooltip_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 19/02/2023 18:06
 */

import { Controller } from '@hotwired/stimulus'
import { Tooltip } from 'bootstrap'

export default class extends Controller {
  static values = {
    placement: String,
  }

  connect() {
    const tooltip = new Tooltip(this.element, {
      trigger: 'hover',
      html: true,
      placement: this.placementValue,
    })
  }
}
