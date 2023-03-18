/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/js/trixEditor.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 14/03/2023 21:49
 */

export default function trixEditor(textarea) {
  const trix = document.getElementById(textarea)
  const _trixEditor = trix.editor

  return _trixEditor.element.innerHTML
}
