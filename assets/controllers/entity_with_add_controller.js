import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['zoneAdd']

  showAdd() {
    this.zoneAddTarget.classList.remove('d-none')
  }
}
