import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['detail']

  async detail(event) {
    if (event.target.dataset.state === 'open') {
      document.getElementById(`detail_composante_${event.params.composante}`).classList.add('d-none')
      this.detailTarget.innerHTML = ''
      event.target.dataset.state = 'close'
      event.target.firstElementChild.classList.add('fa-caret-right')
      event.target.firstElementChild.classList.remove('fa-caret-down')
    } else {
      const response = await fetch(event.params.url)
      this.detailTarget.innerHTML = await response.text()
      document.getElementById(`detail_composante_${event.params.composante}`).classList.remove('d-none')
      event.target.dataset.state = 'open'
      event.target.firstElementChild.classList.remove('fa-caret-right')
      event.target.firstElementChild.classList.add('fa-caret-down')
    }
  }
}
