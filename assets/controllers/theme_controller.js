/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/oreofv2/assets/controllers/theme_controller.js
 * @author davidannebicque
 * @project oreofv2
 * @lastUpdate 02/05/2026 08:54
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['label']

  connect () {
    this._render()
  }

  toggle (event) {
    event.preventDefault()
    const nextTheme = this._currentTheme() === 'dark' ? 'light' : 'dark'
    this._apply(nextTheme)
  }

  _currentTheme () {
    return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light'
  }

  _apply (theme) {
    document.documentElement.setAttribute('data-theme', theme)
    localStorage.setItem('oreof-theme', theme)
    this._render()
  }

  _render () {
    if (!this.hasLabelTarget) {
      return
    }

    const isDark = this._currentTheme() === 'dark'
    this.labelTarget.textContent = isDark ? 'Passer en mode clair' : 'Passer en mode sombre'
  }
}

