import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {

  static values = {
    urlSave: String,
  }

  connect() {

  }

  saveModaliteEnseignement(event) {
    saveData(this.urlSaveValue, {
      action: 'selectWithoutEntity',
      field: 'modaliteEnseignement',
      value: event.target.value,
    })
  }

  saveEcts(event) {
    saveData(this.urlSaveValue,{
      field: 'ects',
      action: 'float',
      value: event.target.value,
    })
  }

  saveVolume(event) {
    console.log(event)
    saveData(this.urlSaveValue,{
      field: event.params.type,
      action: 'float',
      value: event.target.value,
    })
  }
}
