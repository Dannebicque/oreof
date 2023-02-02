import { Controller } from '@hotwired/stimulus'
import { saveData } from '../../js/saveData'

export default class extends Controller {

  static targets = ['detail']

  static values = {
    url: String,
  }

  connect() {
    console.log('step4_controller connect')
    this._loadTroncCommun()
  }

  async _loadTroncCommun() {
    console.log('step4_controller load')
    const response = await fetch(this.urlValue)
    this.detailTarget.innerHTML = await response.text()
  }

}
