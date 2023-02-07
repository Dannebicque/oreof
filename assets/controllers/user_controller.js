import { Controller } from '@hotwired/stimulus'
import callOut from '../js/callOut'

export default class extends Controller {
  static values = {
    url: String,
  }

  async changeRole(event) {
    const data = {
      method: 'POST',
      body: JSON.stringify({
        role: event.target.value,
        checked: event.target.checked,
      }),
    }

    await fetch(this.urlValue, data).then(() => {
      callOut('Rôles mis à jour', 'success')
    })
  }
}
