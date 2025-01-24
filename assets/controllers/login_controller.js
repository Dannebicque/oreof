/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/controllers/login_controller.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 27/04/2023 09:23
 */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
  showFormLogin() {
    document.getElementById('formLogin').classList.toggle('d-none')
  }
}
