import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {
  static values = {
    url: String,
  }

  changeSemestreDebut(event) {
    saveData(this.urlValue, {
      field: 'semestreDebut',
      action: 'int',
      value: event.target.value,
    })
  }
}
