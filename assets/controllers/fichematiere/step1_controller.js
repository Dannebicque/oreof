/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ec/step1_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 15/03/2023 21:14
 */

import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import callOut from '../../js/callOut'
import { calculEtatStep } from '../../js/calculEtatStep'

export default class extends Controller {
  static targets = [
    'content',
    'zoneMutualise',
  ]

  static values = {
    url: String,
    urlMutualise: String,
  }

  connect() {
    this._loadMutualise()
  }

  refreshListe() {
    this._loadMutualise()
  }

  async _loadMutualise() {
    this.zoneMutualiseTarget.innerHTML = window.da.loaderStimulus
    const response = await fetch(this.urlMutualiseValue)
    this.zoneMutualiseTarget.innerHTML = await response.text()
  }

  delete(event) {
    event.preventDefault()
    const { url } = event.params
    const { fiche } = event.params
    let modal = new Modal(document.getElementById('modal-delete'))
    modal.show()
    const btn = document.getElementById('btn-confirm-supprimer')
    btn.replaceWith(btn.cloneNode(true));
    document.getElementById('btn-confirm-supprimer').addEventListener('click', async () => {
      const body = {
        method: 'DELETE',
        body: JSON.stringify({
          field: 'delete',
          fiche,
        }),
      }
      modal = null
      await fetch(url, body).then((e) => {
        if (e.status === 200) {
          callOut('Suppression effectuée', 'success')
          this._loadMutualise()
        } else {
          callOut('Erreur lors de la suppression', 'danger')
        }
      })
    })
    modal = null
  }

  changeResponsableEc(event) {
    this._save({
      field: 'responsableFicheMatiere',
      action: 'responsableFicheMatiere',
      value: event.target.value,
    }).then((data) => {
      if (data.responsableFicheMatiere !== null) {
        document.getElementById('fiche_matiere_resp_dd').innerHTML = `${data.responsableFicheMatiere.display}<br><a href="mailto:${data.responsableFicheMatiere.email}">${data.responsableFicheMatiere.email}</a>`
      } else {
        document.getElementById('fiche_matiere_resp_dd').innerHTML = '<span class="badge bg-danger">Aucun responsable</span>'
      }
    })
  }

  saveSigle(event) {
    this._save({
      field: 'sigle',
      action: 'textarea',
      value: event.target.value,
    }).then((data) => {
      document.getElementById('fiche_matiere_libelle').innerText = data.display
      document.getElementById('fiche_matiere_libelle_dd').innerText = data.display
    })
  }

  saveContenuFr() {
    const { value } = document.getElementById('fiche_matiere_step1_libelle')
    this._save({
      field: 'libelle',
      action: 'textarea',
      value,
    }).then((data) => {
      document.getElementById('fiche_matiere_libelle').innerText = data.display
      document.getElementById('fiche_matiere_libelle_dd').innerText = data.display
    })
  }

  saveCodeApogee() {
    const { value } = document.getElementById('fiche_matiere_step1_codeApogee')
    this._save({
      field: 'codeApogee',
      action: 'textarea',
      value,
    })
  }

  saveContenuEn() {
    this._save({
      field: 'libelleAnglais',
      action: 'textarea',
      value: document.getElementById('fiche_matiere_step1_libelleAnglais').value,
    })
  }

  changeTypeMatiere(event) {
    this._save({
      field: 'typeMatiere',
      action: 'typeMatiere',
      value: event.target.value,
    })
  }

  changeEnseignementMutualise(event) {
    this._save({
      field: 'enseignementMutualise',
      action: 'yesNo',
      value: event.target.value,
    })
    if (parseInt(event.target.value, 10) === 1) {
      document.getElementById('coursMutualises').style.display = 'block'
      this._loadMutualise()
    } else {
      document.getElementById('coursMutualises').style.display = 'none'
    }
  }

  isMutualise(event) {
    this._save({
      field: event.params.type,
      action: 'yesNo',
      value: event.target.value,
    })
  }

  etatStep(event) {
    calculEtatStep(this.urlValue, 1, event, 'ec')
  }

  async _save(options) {
    return saveData(this.urlValue, options).then(async (data) => {
      await updateEtatOnglet(this.urlValue, 'onglet1', 'ec')
      return data
    })
  }
}
