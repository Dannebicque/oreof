import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'
import { updateEtatOnglet } from '../../js/updateEtatOnglet'
import { etatStep } from '../../js/etatStep'

export default class extends Controller {
  static values = {
    url: String,
  }

  etatStep(event) {
    etatStep(this.urlValue, 0, event, 'formation')

    // this._save({
    //   action: 'etatStep',
    //   value: 0,
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
