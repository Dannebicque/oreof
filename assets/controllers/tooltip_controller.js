import { Controller } from '@hotwired/stimulus'
import { Tooltip } from 'bootstrap'

export default class extends Controller {
  static values = {
    placement: String,
  }

  connect() {
    const tooltip = new Tooltip(this.element, {
      trigger: 'hover',
      placement: this.placementValue,
    })
  }
}
