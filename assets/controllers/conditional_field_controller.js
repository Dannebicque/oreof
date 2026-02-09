// assets/controllers/conditional_display_controller.js
import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  static targets = ['trigger', 'container']
  static values = {
    // Liste des valeurs qui déclenchent l'affichage (ex: ["AUTRE", "SPECIFIQUE"])
    expectedValues: Array
  }

  connect () {
    this.toggle() // Gère le point 2 : Affichage correct au chargement
  }

  toggle () {
    const isVisible = this.triggerTargets.some(input => {
      if (input.type === 'checkbox' || input.type === 'radio') {
        // Vérifie si l'input est coché ET si sa valeur est dans la liste attendue
        return input.checked && this.expectedValuesValue.includes(input.value)
      }
      return false
    })

    if (isVisible) {
      this.containerTarget.classList.remove('d-none')
    } else {
      this.containerTarget.classList.add('d-none')
      // Optionnel : vider le champ caché pour l'autosave si masqué
      // this.containerTarget.querySelector('input, textarea')?.value = '';
    }
  }
}
