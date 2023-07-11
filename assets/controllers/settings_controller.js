/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/settings_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/07/2023 15:17
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  changeColor(event) {
    const color = event.currentTarget.dataset.value
    document.getElementsByTagName('html')[0].dataset.color = color
    localStorage.setItem('STORAGE_COLOR', color)
  }
}
