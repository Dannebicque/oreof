import { addCallout } from './callOut'

export const saveData = async (url, options) => {
  const body = {
    method: 'POST',
    body: JSON.stringify(
      options,
    ),
  }

  await fetch(url, body).then(response => response.json()).then(data => {
    if (data === true) {
      addCallout('Sauvegarde effectuée', 'success')
    } else {
      addCallout('Erreur lors de la sauvegarde', 'danger')
    }
  })
}
