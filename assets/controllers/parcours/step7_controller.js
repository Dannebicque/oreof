import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = [
    'content',
  ]

  static values = {
    url: String,
  }
}
