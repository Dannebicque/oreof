import { Controller } from '@hotwired/stimulus'
// import tinymce from 'tinymce/tinymce.min'
// import 'tinymce/plugins/lists/plugin.min'
// import 'tinymce/icons/default/icons.min'
// import 'tinymce/models/dom'
//
// // // A theme is also required
// import 'tinymce/themes/silver/theme.min'
// import '../vendor/tinyMceLang/fr_FR'

export default class extends Controller {
  static targets = ['input', 'texte']

  initialize() {
    this.update = this.update.bind(this)
  }

  connect() {
    this.update()
    this.inputTarget.addEventListener('input', this.update)
    // this.initTinyMCE()
  }

  // initTinyMCE() {
  //   tinymce.init({
  //     selector: '.tinyMce',
  //     base_url: '/build/tinymce',
  //     menubar: false,
  //     toolbar: 'undo redo | bold italic',
  //     setup(editor) {
  //       editor.on('change', (e) => {
  //         this.dispatch('change')
  //         this.changeTextArea(e)
  //       });
  //     },
  //   })
  // }
  //
  // changeTextArea(e) {
  //   console.log('change')
  // }

  disconnect() {
    this.inputTarget.removeEventListener('input', this.update)
    // tinymce.remove()
  }

  update() {
    this.texteTarget.innerHTML = `${this.count.toString()} caractères restants`
  }

  get count() {
    const value = this.inputTarget.value.length
    return Math.max(this.maxLength - value, 0)
  }

  get maxLength() {
    return this.inputTarget.maxLength
  }
}
