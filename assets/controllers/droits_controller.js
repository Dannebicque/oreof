import { Controller } from '@hotwired/stimulus'
import callOut from '../js/callOut'

export default class extends Controller {
  static values = {
    url: String,
  }

  sauvegarde(event) {
    const data = {
      method: 'POST',
      body: JSON.stringify({
        code: event.target.value,
        checked: event.target.checked,
        role: event.params.role,
      }),
    }

    fetch(this.urlValue, data).then(() => {
      callOut('Droits sauvegardés', 'success')
    })
  }
}
