import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'

export default class extends Controller {
  static values = {
    urlSave: String,
  }

  saveModaliteEnseignement(event) {
    saveData(this.urlSaveValue, {
      action: 'modalitesEnseignement',
      value: event.target.value,
    })
  }

  saveEcts(event) {
    saveData(this.urlSaveValue, {
      field: 'ects',
      action: 'float',
      value: event.target.value,
    })
  }

  saveVolume(event) {
    saveData(this.urlSaveValue, {
      field: event.params.type,
      action: 'float',
      value: event.target.value,
    })
  }

  async _save(options) {
    await saveData(this.urlSaveValue, options).then(async () => {
      await updateEtatOnglet(this.urlSaveValue, 'onglet4', 'ec')
    })
  }
}
