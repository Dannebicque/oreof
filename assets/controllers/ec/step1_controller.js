import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {
  static targets = [
    'content',
  ]
  static values = {
    url: String,
  }

  changeResponsableEc(event) {
//todo:....
  }
  saveContenuFr(event) {
    saveData(this.urlValue, {
      field: 'libelle',
      action: 'textarea',
      value: document.getElementById('ec_step1_libelle').value,
    })
  }

  saveContenuEn(event) {
    saveData(this.urlValue, {
      field: 'libelleAnglais',
      action: 'textarea',
      value: document.getElementById('ec_step1_libelleAnglais').value,
    })
  }

  changeEnseignementMutualise(event) {
    saveData(this.urlValue,
      {
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
    saveData(this.urlValue,
      {
        field: event.params.type,
        action: 'yesNo',
        value: event.target.value,
      })
  }
}
