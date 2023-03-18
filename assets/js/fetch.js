/*
 * Copyright (c) 2023. | David Annebicque | ORéOF  - All Rights Reserved
 * @file /Users/davidannebicque/Sites/oreof/assets/js/fetch.js
 * @author davidannebicque
 * @project oreof
 * @lastUpdate 03/02/2023 08:59
 */

const request = (url, params = {}, method = 'GET') => {
  const options = {
    method,
  }
  if (method === 'GET') {
    url += `?${(new URLSearchParams(params)).toString()}`
  } else {
    options.body = JSON.stringify(params)
  }

  return fetch(url, options).then((response) => response.json())
}

export const get = (url, params) => request(url, params, 'GET')
export const post = (url, params) => request(url, params, 'POST')
