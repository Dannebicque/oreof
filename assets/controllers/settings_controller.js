/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/settings_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 11/07/2023 15:17
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  connect() {
    const dyslexiqueCheckbox = document.getElementById('dyslexique');
    const storageDyslexique = localStorage.getItem('acorn-standard-dyslexique');

    if (storageDyslexique === 'true') {
      dyslexiqueCheckbox.checked = true;
    } else if (storageDyslexique === 'false') {
      dyslexiqueCheckbox.checked = false;
    }
  }

  changeColor(event) {
    const color = event.currentTarget.dataset.value
    document.getElementsByTagName('html')[0].dataset.color = color
    localStorage.setItem('acorn-standard-color', color)
  }

  changeDyslexique(event) {
    document.getElementsByTagName('html')[0].dataset.dyslexique = event.target.checked
    localStorage.setItem('acorn-standard-dyslexique', event.target.checked)
  }
}
