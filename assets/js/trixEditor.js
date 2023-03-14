export default function trixEditor(textarea) {
  const trix = document.getElementById(textarea)
  const _trixEditor = trix.editor

  return _trixEditor.element.innerHTML
}
