import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['detail']

  async detail(event) {
    const response = await fetch(event.params.url)
    this.detailTarget.innerHTML = await response.text()
    document.getElementById('detail_composante_'+event.params.composante).classList.remove('d-none')
  }


}
