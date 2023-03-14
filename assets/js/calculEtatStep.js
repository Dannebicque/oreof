import { saveData } from './saveData'
import { updateEtatOnglet } from './updateEtatOnglet'

export const calculEtatStep = async (url, step, event, prefix) => {
  console.log('debut')
  console.log(event)
  await saveData(url, {
    action: 'etatStep',
    value: step,
    isChecked: event.target.checked,
  }).then(async (data) => {
    console.log('---a---')
    console.log(event)
    if (document.getElementById('alert-error')) {
      document.getElementById('alert-error').remove()
    }
    if (data === true) {
      console.log('---b---')
      await updateEtatOnglet(url, `onglet${step}`, prefix)// todo: mettre à la fin
      const parent = event.target.closest('.alert')
      if (event.target.checked) {
        parent.classList.remove('alert-warning')
        parent.classList.add('alert-success')
      } else {
        parent.classList.remove('alert-success')
        parent.classList.add('alert-warning')
      }
    } else {
      console.log('---c---')
      console.log(event.target.id)
      console.log(event.target.checked)
      // event.target.checked = false
      document.getElementById('etatStructure').checked = false
      console.log(event.target.id)
      console.log(event.target.checked)
      console.log(data)
      let liste = '<ul>'
      data.error.forEach((error) => {
        liste += `<li>${error}</li>`
      })
      liste += '</ul>'

      const zone = document.getElementById('alertEtatStructure')

      zone.innerHTML += `
            <div class="alert alert-danger border-2 d-flex align-items-center mt-2" role="alert" id="alert-error">
          <div class="bg-danger me-3 icon-item"><span class="fas fa-times-circle text-white fs-3"></span></div>
          <p class="mb-0 flex-1">${liste}</p>
      </div>`
      console.log('fin')
    }
  })
}
