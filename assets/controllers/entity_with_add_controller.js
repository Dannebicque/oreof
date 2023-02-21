import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['zoneAdd']

  connect() {
    console.log('EntityWithAddController#connect')
  }

  showAdd() {
    this.zoneAddTarget.classList.remove('d-none')
  }
}
