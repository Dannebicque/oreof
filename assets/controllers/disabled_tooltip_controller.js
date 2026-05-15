/*
 * Copyright (c) 2026. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/oreofv2/assets/controllers/disabled_tooltip_controller.js
 * @author davidannebicque
 * @project oreofv2
 * @lastUpdate 15/05/2026 08:55
 */

import { Controller } from '@hotwired/stimulus'
import * as bootstrap from 'bootstrap'

/**
 * Ce contrôleur résout le problème des tooltips qui ne se déclenchent pas sur les éléments disabled.
 *
 * Les éléments disabled ne reçoivent pas les événements souris, ce qui empêche les tooltips Bootstrap de fonctionner.
 *
 * Utilisation :
 * <div data-controller="disabled-tooltip">
 *   <button type="button" disabled data-bs-toggle="tooltip" data-bs-title="Raison">
 *     Mon bouton désactivé
 *   </button>
 * </div>
 *
 * OU pour une meilleure UX avec curseur:
 * <span class="d-inline-block" data-bs-toggle="tooltip" data-bs-title="Raison" style="cursor: not-allowed;">
 *   <button type="button" disabled>
 *     Mon bouton désactivé
 *   </button>
 * </span>
 */
export default class extends Controller {
  connect () {
    this.wrapDisabledElements()
    this.initializeTooltips()
  }

  /**
   * Enveloppe chaque élément disabled trouvé dans un span avec le tooltip
   */
  wrapDisabledElements () {
    const disabledElements = this.element.querySelectorAll('[disabled]')

    disabledElements.forEach((el) => {
      // On ne wrapp que si l'élément a un tooltip
      if (!el.hasAttribute('data-bs-toggle') || el.getAttribute('data-bs-toggle') !== 'tooltip') {
        return
      }

      // Vérifie si l'élément est déjà wrappé
      const parent = el.parentElement
      if (parent && parent.hasAttribute('data-bs-toggle') && parent.getAttribute('data-bs-toggle') === 'tooltip') {
        return
      }

      // Crée un span wrapper
      const wrapper = document.createElement('span')
      wrapper.className = 'd-inline-block'
      wrapper.style.cursor = 'not-allowed'

      // Transfère les attributs du tooltip du bouton au wrapper
      if (el.hasAttribute('data-bs-title')) {
        wrapper.setAttribute('data-bs-title', el.getAttribute('data-bs-title'))
      }
      if (el.hasAttribute('data-bs-placement')) {
        wrapper.setAttribute('data-bs-placement', el.getAttribute('data-bs-placement'))
      }
      if (el.hasAttribute('data-bs-html')) {
        wrapper.setAttribute('data-bs-html', el.getAttribute('data-bs-html'))
      }
      if (el.hasAttribute('data-bs-delay')) {
        wrapper.setAttribute('data-bs-delay', el.getAttribute('data-bs-delay'))
      }

      // Ajoute le data-bs-toggle au wrapper
      wrapper.setAttribute('data-bs-toggle', 'tooltip')

      // Wrapper l'élément
      el.parentNode.insertBefore(wrapper, el)
      wrapper.appendChild(el)

      // Retire les attributs du tooltip du bouton
      el.removeAttribute('data-bs-toggle')
      el.removeAttribute('data-bs-title')
      el.removeAttribute('data-bs-placement')
    })
  }

  /**
   * Initialise les tooltips du conteneur
   */
  initializeTooltips () {
    const tooltipElements = this.element.querySelectorAll('[data-bs-toggle="tooltip"]')
    tooltipElements.forEach((el) => {
      bootstrap.Tooltip.getOrCreateInstance(el)
    })
  }
}

