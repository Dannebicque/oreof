export const updateEtatOnglet = async (url, onglet, prefix) => {
  const body = {
    method: 'POST',
    body: JSON.stringify({
      action: 'stateOnglet',
      onglet,
    }),
  }

  await fetch(url, body).then((response) => response.json()).then((data) => {
    document.getElementById(`${prefix}_${onglet}`).classList.remove('state-complete')
    document.getElementById(`${prefix}_${onglet}`).classList.remove('state-en-cours')
    document.getElementById(`${prefix}_${onglet}`).classList.remove('state-vide')
    document.getElementById(`${prefix}_${onglet}`).classList.add(`state-${data}`)
  })
}
