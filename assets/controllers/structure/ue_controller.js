/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/structure/ue_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 12/03/2023 16:12
 */

import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'
import { saveData } from '../../js/saveData'
import callOut from '../../js/callOut'

export default class extends Controller {
  static targets = ['detail']

  static values = {
    ue: Number,
    parcours: Number,
    url: String,
  }

  async deplacerUe(event) {
    event.preventDefault()
    const { url } = event.params
    await fetch(url).then(() => {
      callOut('UE déplacée', 'success')
      this.dispatch('refreshListe')
    })
  }

  detail(event) {
    if (event.target.dataset.state === 'open') {
      document.getElementById(`detail_ue_${event.params.ue}_${event.params.parcours}`).classList.add('d-none')
      this.detailTarget.innerHTML = ''
      event.target.dataset.state = 'close'
      event.target.firstElementChild.classList.add('fa-caret-right')
      event.target.firstElementChild.classList.remove('fa-caret-down')
    } else {
      this._listeEc(event)
      event.target.dataset.state = 'open'
      event.target.firstElementChild.classList.remove('fa-caret-right')
      event.target.firstElementChild.classList.add('fa-caret-down')
    }
  }

  async refreshListeEc(event) { // l'event pourrait emetre le numéro d'UE et de parcours
    if (event.detail.ue === this.ueValue && event.detail.parcours === this.parcoursValue) {
      await this._listeEc(event)
      // mise à jour des ECTS de l'UE et du Semestre
      const response = await fetch(this.urlValue)
      const data = await response.json()
      const ectsUe = document.getElementById(`ects_ue_${event.detail.ue}_${event.detail.parcours}`)
      const ectsSemestre = document.getElementById(`ects_semestre_${data.idSemestre}_${event.detail.parcours}`)
      if (data.ue > 0 && data.ue < 30) {
        // todo: vérifier les valeurs avec le type de diplôme (6 pour les L par exemple)
        ectsUe.innerHTML = `<span class="badge bg-success me-2">${data.ue} ECTS</span>`
      } else {
        ectsUe.innerHTML = `<span class="badge bg-danger me-2">${data.ue} ECTS</span>`
      }

      if (data.semestre === 30) {
        ectsSemestre.innerHTML = `<span class="badge bg-success me-2">${data.semestre} ECTS</span>`
      } else {
        ectsSemestre.innerHTML = `<span class="badge bg-danger me-2">${data.semestre} ECTS</span>`
      }
    }
  }

  async _listeEc(event) {
    const response = await fetch(event.params.url)
    this.detailTarget.innerHTML = await response.text()
    document.getElementById(`detail_ue_${event.params.ue}_${event.params.parcours}`).classList.remove('d-none')
  }

  changeNatureUe(event) {
    // récupérer data-choix sur la balise option selectionnée

    const { choix } = event.target.options[event.target.selectedIndex].dataset
    if (choix === 'true') {
      if (confirm('Attention, vous allez changer la nature de l\'UE pour une UE impliquant plusieurs choix. Vous devez définir au moins deux UE de choix. Souhaitez-vous continuer ?')) {
        saveData(event.params.url, {
          actions: 'changeNatureUe',
          value: event.target.value,
        })
        // todo: et bouton pour en ajouter d'autres ??? en dessous ? Gérer le déplacement dans l'ordre sans le dépasser, gérer la supprssion...
        // todo: problème de refresh de la liste des UE...
        this.dispatch('refreshListe')
      }
    } else {
      saveData(event.params.url, {
        actions: 'changeNatureUe',
        value: event.target.value,
      })
    }
  }

  delete(event) {
    event.preventDefault()
    const { url } = event.params
    const { csrf } = event.params
    let modal = new Modal(document.getElementById('modal-delete'))
    modal.show()
    document.getElementById('btn-confirm-supprimer').addEventListener('click', async () => {
      const body = {
        method: 'DELETE',
        body: JSON.stringify({
          csrf,
        }),
      }
      modal = null
      await fetch(url, body).then(() => {
        callOut('Suppression effectuée', 'success')
        this.dispatch('refreshListe')
      })
    })
  }
}
