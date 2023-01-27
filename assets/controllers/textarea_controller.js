import { Controller } from '@hotwired/stimulus'
import { Modal } from 'bootstrap'
import { addCallout } from '../js/callOut'

export default class extends Controller {
  static targets = ['input', 'texte']

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
    this.texteTarget.innerHTML = this.count.toString() + " caractères restants"
  }

  get count() {
    console.log(this.inputTarget)
    let value = this.inputTarget.value.length
    return Math.max(this.maxLength - value, 0)
  }

  get maxLength() {
    return this.inputTarget.maxLength
  }
}
