/*
 * Copyright (c) 2025. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/offre_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 28/10/2025 09:56
 */

import { Controller } from '@hotwired/stimulus'
import callOut from '../js/callOut'
import JsonResponse from '../js/JsonResponse'

export default class extends Controller {
  static values = {
    url: String,
  }

  scanParcours () {
    const rows = this.element.querySelectorAll('[data-parcours-open]')
    rows.forEach(row => {
      const openValue = row.dataset.parcoursOpen
      const open = openValue === 'OUVERT'

      const parcoursId = row.dataset.parcoursId
      if (parcoursId) {
        const extraBlocks = this.element.querySelectorAll(`[data-parcours-id="${parcoursId}"]`)
        extraBlocks.forEach(block => {
          if (block === row) return
          const extraControls = block.querySelectorAll('input[type="checkbox"], input[type="radio"], select, input:not([type]), input[type="text"], button')
          extraControls.forEach(el => {
            el.disabled = !open
            if (!open) {
              el.setAttribute('aria-disabled', 'true')
            } else {
              el.removeAttribute('aria-disabled')
            }
          })
        })
      }
    })
  }

  scanFormation () {
    const rows = this.element.querySelectorAll('[data-formation-open]')
    rows.forEach(row => {
      const openValue = row.dataset.formationOpen
      const open = openValue === 'OUVERT'

      const parcoursId = row.dataset.formationId
      if (parcoursId) {
        const extraBlocks = this.element.querySelectorAll(`[data-formation-id="${parcoursId}"]`)
        extraBlocks.forEach(block => {
          if (block === row) return
          const extraControls = block.querySelectorAll('input[type="checkbox"], input[type="radio"], select, input:not([type]), input[type="text"], button')
          extraControls.forEach(el => {
            el.disabled = !open
            if (!open) {
              el.setAttribute('aria-disabled', 'true')
            } else {
              el.removeAttribute('aria-disabled')
            }
          })
        })
      }
    })
  }

  async changeOuverture (event) {
    const body = {
      method: 'POST',
      body: JSON.stringify({
        value: event.target.value,
        id: event.params.id,
        action: 'changeOuverture'
      }),
    }

    await fetch(this.urlValue, body).then((response) => JsonResponse(response))

    //si id commence par parcours_
    if (event.params.id.startsWith('parcours_')) {
      const tr = event.target.closest('tr')
      if (tr) {
        tr.dataset.parcoursOpen = event.target.value
      }
      this.scanParcours()
    }

    if (event.params.id.startsWith('formation_')) {
      const tr = event.target.closest('tr')
      if (tr) {
        tr.dataset.formationOpen = event.target.value
      }
      this.scanFormation()
    }
  }

  async changeOuvertureAnnee (event) {
    const body = {
      method: 'POST',
      body: JSON.stringify({
        id: event.params.id,
        action: 'changeOuvertureAnnee'
      }),
    }

    await fetch(this.urlValue, body).then((response) => JsonResponse(response))

    const tr = event.target.closest('tr')
    if (tr) {
      const open = event.target.checked
      const controls = tr.querySelectorAll('input[type="checkbox"], input[type="radio"], select, input:not([type]), input[type="text"], button')
      controls.forEach(el => {
        if (el === event.target) return
        el.disabled = !open
        if (!open) {
          el.setAttribute('aria-disabled', 'true')
        } else {
          el.removeAttribute('aria-disabled')
        }
      })
    }

  }

}
