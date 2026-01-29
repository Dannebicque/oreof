// // assets/controllers/inline_create_controller.js
// import { Controller } from '@hotwired/stimulus'
//
// export default class extends Controller {
//   static targets = ['select', 'newInput', 'toggleButton']
//
//   connect() {
//     this.updateButtonLabel()
//   }
//
//   toggleNewField() {
//     this.newInputTarget.classList.toggle('hidden')
//     this.selectTarget.classList.toggle('hidden')
//
//     if (!this.newInputTarget.classList.contains('hidden')) {
//       this.newInputTarget.focus()
//     }
//
//     this.updateButtonLabel()
//   }
//
//   updateButtonLabel() {
//     const isCreating = !this.newInputTarget.classList.contains('hidden')
//     this.toggleButtonTarget.textContent = isCreating
//       ? '← Revenir à la liste'
//       : '+ Créer nouveau'
//   }
// }

// assets/controllers/inline_create_controller.js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['select', 'newInput', 'toggleButton']

  connect () {
    this.applyState()
    this.updateButtonLabel()
  }

  toggleNewField () {
    this.newInputTarget.classList.toggle('hidden')
    this.selectTarget.classList.toggle('hidden')

    // focus
    if (!this.newInputTarget.classList.contains('hidden')) {
      this.newInputTarget.focus()
    }

    this.applyState()
    this.updateButtonLabel()
  }

  applyState () {
    const creating = !this.newInputTarget.classList.contains('hidden')

    const originalRequired =
      (this.selectTarget.dataset.inlineCreateOriginalRequired === '1')

    if (creating) {
      // Select caché -> on le rend inoffensif pour la validation HTML5
      this.selectTarget.required = false
      this.selectTarget.disabled = true

      // Input visible -> devient required si c’était requis
      this.newInputTarget.disabled = false
      this.newInputTarget.required = originalRequired
    } else {
      // Mode select
      this.selectTarget.disabled = false
      this.selectTarget.required = originalRequired

      // Input caché
      this.newInputTarget.required = false
      this.newInputTarget.disabled = true
      this.newInputTarget.value = ''
    }
  }

  updateButtonLabel () {
    const creating = !this.newInputTarget.classList.contains('hidden')
    this.toggleButtonTarget.textContent = creating ? '← Revenir à la liste' : '+ Créer nouveau'
  }
}
