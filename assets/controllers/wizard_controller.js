/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/formation_wizard_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/02/2023 22:20
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['content', 'tab']

  static values = {
    url: String,
    step: String,
    stepDefault: String,
  }

  connect() {
    this._onPopState = this._handlePopState.bind(this)
    window.addEventListener('popstate', this._onPopState)

    if (this.stepValue === '') {
      const urlParams = new URLSearchParams(window.location.search)
      this.stepValue = urlParams.has('step') ? urlParams.get('step') : this.stepDefaultValue
    }

    this._activateStep(this.stepValue)
    this._loadStep(this.stepValue)
  }

  disconnect () {
    window.removeEventListener('popstate', this._onPopState)
  }

  changeStep(event) {
    event.preventDefault()

    const step = event.params.step ?? event.currentTarget?.dataset.step
    if (!step) {
      return
    }

    const url = new window.URL(window.location)
    const params = new URLSearchParams(url.search)
    params.set('step', step)
    window.history.pushState({}, '', `${url.pathname}?${params.toString()}`)

    this.stepValue = step
    this._activateStep(step)
    this._loadStep(step)
  }

  _handlePopState () {
    const urlParams = new URLSearchParams(window.location.search)
    const step = urlParams.get('step') || this.stepDefaultValue

    this.stepValue = step
    this._activateStep(step)
    this._loadStep(step)
  }

  _activateStep (step) {
    if (!this.hasTabTarget) {
      return
    }

    this.tabTargets.forEach((tab) => {
      const tabStep = tab.dataset.step || tab.dataset.wizardStepParam
      const isActive = String(tabStep) === String(step)

      tab.classList.toggle('active', isActive)
      tab.classList.toggle('app-tab-active', isActive)
      tab.setAttribute('aria-selected', isActive ? 'true' : 'false')
      tab.setAttribute('tabindex', isActive ? '0' : '-1')
    })
  }

  async _loadStep(step) {
    const url = new window.URL(this.urlValue, window.location.origin)
    url.searchParams.set('step', step)

    if (this.hasContentTarget && this.contentTarget.tagName === 'TURBO-FRAME') {
      this.contentTarget.setAttribute('src', url.toString())
      return
    }

    const response = await fetch(url.toString(), {
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
      },
    })
    this.contentTarget.innerHTML = await response.text()
  }
}
