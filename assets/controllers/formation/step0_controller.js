import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { calculEtatStep } from '../../js/calculEtatStep'

export default class extends Controller {
  static values = {
    url: String,
  }

  etatStep(event) {
    calculEtatStep(this.urlValue, 0, event, 'formation')
  }

  changeSemestreDebut(event) {
    saveData(this.urlValue, {
      field: 'semestreDebut',
      action: 'int',
      value: event.target.value,
    })
    updateEtatOnglet(this.urlValue, 'onglet0')
  }

  async _save(options) {
    await saveData(this.urlValue, options).then(async () => {
      await updateEtatOnglet(this.urlValue, 'onglet0', 'formation')
    })
  }
}
