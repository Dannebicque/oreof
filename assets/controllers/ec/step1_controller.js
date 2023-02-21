import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
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
      value: document.getElementById('ec_step1_libelle').value,
    })
  }

  saveContenuEn() {
    this._save({
      field: 'libelleAnglais',
      action: 'textarea',
      value: document.getElementById('ec_step1_libelleAnglais').value,
    })
  }

  changeEnseignementMutualise(event) {
    this._save({
      field: 'enseignementMutualise',
      action: 'yesNo',
      value: event.target.value,
    })
    if (event.target.value == 1) {
      document.getElementById('coursMutualises').style.display = 'block'
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

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet1', 'ec')
    })
  }
}
