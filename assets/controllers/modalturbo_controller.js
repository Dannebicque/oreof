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

  // static targets = ["wrapper", "frame"]
  //
  connect () {
    this.closeHandler = () => this.hide()
    window.addEventListener('modal:close', this.closeHandler)
  }

  disconnect () {
    window.removeEventListener('modal:close', this.closeHandler)
  }

  //
  // open(event) {
  //   const url = event.currentTarget.dataset.modalUrl;
  //   if (!url) return;
  //   // show wrapper and load turbo-frame content
  //   this.wrapperTarget.classList.remove('hidden');
  //   this.frameTarget.src = url;
  // }
  //
  // hide() {
  //   // clear frame and hide wrapper
  //   try { this.frameTarget.removeAttribute('src'); } catch(e){}
  //   this.wrapperTarget.classList.add('hidden');
  // }
}

