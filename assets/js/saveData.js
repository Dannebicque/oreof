import callOut from './callOut'

export const saveData = async (url, options) => {
  const body = {
    method: 'POST',
    body: JSON.stringify(
      options,
    ),
  }

  return fetch(url, body).then((response) => response.json()).then((data) => {
    if (data === true) {
      callOut('Sauvegarde effectuée', 'success')
    } else if (data === false) {
      callOut('Erreur lors de la sauvegarde', 'danger')
    }
    return data
  })
}
