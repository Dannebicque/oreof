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
import trixEditor from '../../js/trixEditor'

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
    document.getElementById('ec_step1_libelle').addEventListener('trix-blur', this.saveContenuFr.bind(this))
    document.getElementById('ec_step1_libelleAnglais').addEventListener('trix-blur', this.saveContenuEn.bind(this))
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
    const { ue } = event.params
    let modal = new Modal(document.getElementById('modal-delete'))
    modal.show()
    const btn = document.getElementById('btn-confirm-supprimer')
    btn.replaceWith(btn.cloneNode(true));
    document.getElementById('btn-confirm-supprimer').addEventListener('click', async () => {
      const body = {
        method: 'DELETE',
        body: JSON.stringify({
          field: 'delete',
          ue,
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
      field: 'responsableEc',
      action: 'responsableEc',
      value: event.target.value,
    }).then(() => {
      // dispatch pour mettre à jour le bloc de la page
      this.dispatch('refreshSynthese', { bubbles: true })
    })
  }

  saveContenuFr() {
    this._save({
      field: 'libelle',
      action: 'textarea',
      value: trixEditor('ec_step1_libelle'),
    })
  }

  saveContenuEn() {
    this._save({
      field: 'libelleAnglais',
      action: 'textarea',
      value: trixEditor('ec_step1_libelleAnglais'),
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

    // this._save({
    //   action: 'etatStep',
    //   value: 1,
    //   isChecked: event.target.checked,
    // })
    //
    // const parent = event.target.closest('.alert')
    // if (event.target.checked) {
    //   parent.classList.remove('alert-warning')
    //   parent.classList.add('alert-success')
    // } else {
    //   parent.classList.remove('alert-success')
    //   parent.classList.add('alert-warning')
    // }
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet1', 'ec')
    })
  }
}
