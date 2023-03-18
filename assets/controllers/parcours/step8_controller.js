// /*
//  * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
//  * @file /Users/davidannebicque/Sites/oreof/assets/controllers/parcours/step8_controller.js
//  * @author davidannebicque
//  * @project oreof
//  * @lastUpdate 16/03/2023 15:00
//  */
//
// import { Controller } from '@hotwired/stimulus'
// import { saveData } from '../../js/saveData'
// import { updateEtatOnglet } from '../../js/updateEtatOnglet'
// import { calculEtatStep } from '../../js/calculEtatStep'
// import trixEditor from '../../js/trixEditor'
//
// export default class extends Controller {
//   static targets = [
//     'content',
//   ]
//
//   static values = {
//     url: String,
//   }
//
//   connect() {
//     document.getElementById('parcours_step8_coordSecretariat').addEventListener('trix-blur', this.coordSecretariat.bind(this))
//   }
//
//   coordSecretariat() {
//     this._save({
//       field: 'coordSecretariat',
//       action: 'textarea',
//       value: trixEditor('parcours_step8_coordSecretariat'),
//     })
//   }
//
//   async _save(options) {
//     await saveData(this.urlValue, options).then(async () => {
//       await updateEtatOnglet(this.urlValue, 'onglet8', 'parcours')
//     })
//   }
//
//   etatStep(event) {
//     calculEtatStep(this.urlValue, 8, event, 'parcours')
//   }
// }
