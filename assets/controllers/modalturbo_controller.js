/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/modalturbo_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 13/01/2026 19:11
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['wrapper']

  open () {
    this.wrapperTarget.classList.remove('hidden')
    document.documentElement.classList.add('overflow-hidden')
  }

  close () {
    this.wrapperTarget.classList.add('hidden')
    document.documentElement.classList.remove('overflow-hidden')
  }

  connect () {
    this.closeHandler = () => this.close()
    window.addEventListener('modal:close', this.closeHandler)
  }

  disconnect () {
    window.removeEventListener('modal:close', this.closeHandler)
  }
}

