/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/ue_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 26/03/2023 11:22
 */

import { Controller } from '@hotwired/stimulus'
import { useDebounce } from 'stimulus-use'
import { saveData } from '../js/saveData';

export default class extends Controller {
  static debounces = ['changeNatureUeEcTexte', 'changeTypeUeTexte'];

  connect() {
    useDebounce(this)
  }

  ajoutTypeUe(event) {
    event.preventDefault()
    document.getElementById('typeUeTexte').classList.remove('d-none')
  }

  changeTypeUeTexte(event) {
    event.preventDefault()
    document.getElementById('ue_typeUe').disabled = event.currentTarget.value.length > 0
  }

  changeNatureUe(event) {
    // récupérer data-choix sur la balise option selectionnée

    const { choix } = event.target.options[event.target.selectedIndex].dataset
    const { libre } = event.target.options[event.target.selectedIndex].dataset
    if (choix === 'true') {
      if (confirm('Attention, vous allez changer la nature de l\'UE pour une UE impliquant plusieurs choix. Vous devez définir au moins deux UE de choix. Souhaitez-vous continuer ?')) {
        document.getElementById('descriptionUeLibre').classList.add('d-none')
      }
    } else if (libre === 'true') {
      if (confirm('Attention, vous allez changer la nature de l\'UE. Souhaitez-vous continuer ?')) {
        document.getElementById('descriptionUeLibre').classList.remove('d-none')
      }
    } else {
      document.getElementById('descriptionUeLibre').classList.add('d-none')
    }
  }
}
