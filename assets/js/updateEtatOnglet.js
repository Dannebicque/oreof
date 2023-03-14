export const updateEtatOnglet = async (url, onglet, prefix) => {
  const body = {
    method: 'POST',
    body: JSON.stringify({
      action: 'stateOnglet',
      onglet,
    }),
  }

  await fetch(url, body).then((response) => response.json()).then((data) => {
    console.log(data)
    document.getElementById(`${prefix}_${onglet}`).classList.remove('state-complete')
    document.getElementById(`${prefix}_${onglet}`).classList.remove('state-en-cours')
    document.getElementById(`${prefix}_${onglet}`).classList.remove('state-vide')
    document.getElementById(`${prefix}_${onglet}`).classList.add(`state-${data}`)
    if (data === 'en-cours' || data === 'vide') {
      // remise à 0 de l'état
      document.getElementById('etatStructure').checked = false
      document.getElementById('alertEtatStructure').classList.remove('alert-success')
      document.getElementById('alertEtatStructure').classList.add('alert-warning')
    }
  })
}
