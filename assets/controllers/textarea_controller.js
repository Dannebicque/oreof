import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['input', 'texte']

  static values = { maxLength: Number }

  initialize() {
    this.update = this.update.bind(this)
  }

  connect() {
    this.update()
    this.inputTarget.addEventListener('input', this.update)
  }

  disconnect() {
    this.inputTarget.removeEventListener('input', this.update)
  }

  update() {
    this.texteTarget.innerHTML = `${this.count.toString()} caractères restants`
  }

  get count() {
    const value = this.inputTarget.value.length
    return Math.max(this.maxLengthValue - value, 0)
  }
}
