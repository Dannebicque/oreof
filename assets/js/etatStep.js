import { saveData } from './saveData'
import { updateEtatOnglet } from './updateEtatOnglet'

export const etatStep = async (url, step, event, prefix) => {
  await saveData(url, {
    action: 'etatStep',
    value: step,
    isChecked: event.target.checked,
  }).then(async (data) => {
    console.log(data)
    if (data === true) {
      await updateEtatOnglet(url, `onglet${step}`, prefix)
      const parent = event.target.closest('.alert')
      if (event.target.checked) {
        parent.classList.remove('alert-warning')
        parent.classList.add('alert-success')
      } else {
        parent.classList.remove('alert-success')
        parent.classList.add('alert-warning')
      }
    } else {
      event.target.checked = false
      if (document.getElementById('alert-error')) {
        document.getElementById('alert-error').remove()
      }
      event.target.parentNode.parentNode.innerHTML += `
      <div class="alert alert-danger border-2 d-flex align-items-center mt-2" role="alert" id="alert-error">
    <div class="bg-danger me-3 icon-item"><span class="fas fa-times-circle text-white fs-3"></span></div>
    <p class="mb-0 flex-1">${data.error}</p>
</div>`
    }
  })
}
