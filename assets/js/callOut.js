/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/js/callOut.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 03/02/2023 09:18
 */

import Toast from '../components/Toast'

export default function callOut(message, label) {
  switch (label) {
    case 'success':
      Toast.success(message)
      break
    case 'danger':
    case 'error':
      Toast.error(message)
      break
    case 'warning':
      Toast.warning(message)
      break
    case 'info':
      Toast.info(message)
      break
    default:
      Toast.info(message)
  }
}
