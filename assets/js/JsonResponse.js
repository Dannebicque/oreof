/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/js/reponse.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 10/06/2023 09:11
 */

import callOut from './callOut';

export default async function JsonResponse(reponse) {
  const data = await reponse.json()
  if (reponse.status === 200) {
    callOut(data.message, 'success')
    return data
  }

  if (reponse.status === 500) {
    callOut(data.message, 'error')
    return data
  }
}
