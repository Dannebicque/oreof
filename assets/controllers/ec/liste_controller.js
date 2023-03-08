import { Controller } from '@hotwired/stimulus'
import callOut from '../../js/callOut'

export default class extends Controller {
  async deplacerEc(event) {
    event.preventDefault()
    const { url } = event.params
    await fetch(url).then(() => {
      callOut('EC déplacé', 'success')
      this.dispatch('refreshListeEc')
    })
  }
}
