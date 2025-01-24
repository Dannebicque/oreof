document.addEventListener('DOMContentLoaded', (e) => {
  const ficheMatiereUrl = document.querySelector('#rootListElement').getAttribute('data-fiche-matiere-url');

  const links = document.querySelectorAll('.fiche-matiere-search-link');

  links.forEach((link) => {
    link.addEventListener('click', (event) => {
      const { target } = event;
      const fetchUrl = target.getAttribute('data-fetch-url');
      const modalTitleElement = document.querySelector('#titre-modale-recherche');

      modalTitleElement.innerHTML = '';

      const listNode = document.querySelector('#associated-fiche-matiere-modal-body')
      // Empty node list
      while (listNode.hasChildNodes()) {
        listNode.removeChild(listNode.firstChild);
      }

      // loading icon
      const loadingIcon = document.createElement('i');
      loadingIcon.className = 'fa-duotone fa-spinner spinning-icon';
      listNode.appendChild(loadingIcon);

      // fetching result
      fetch(fetchUrl)
        .then((response) => response.json())
        .then((jsonArray) => {
          loadingIcon.remove();

          const fichesNumber = Number(target.getAttribute('data-number-associated-fiches-matieres'));
          if (fichesNumber >= 2) {
            modalTitleElement.innerHTML = `${fichesNumber} fiches matières associées`;
          } else if (fichesNumber === 1) {
            modalTitleElement.innerHTML = '1 fiche matière associée'
          }

          const listParent = document.createElement('ul');
          jsonArray.forEach((ficheMatiere) => {
            const listElement = document.createElement('li');
            listElement.className = 'my-2'

            const link = document.createElement('a');
            // link.addAttribute('target', '_blank');
            link.innerHTML = ficheMatiere.libelle;
            link.href = ficheMatiereUrl.replace('%C2%B5%23+', ficheMatiere.slug);
            link.target = '_blank';

            listElement.appendChild(link);
            listParent.appendChild(listElement);
          });
          listNode.appendChild(listParent);
        })
      // handling AJAX error
        .catch((error) => {
          loadingIcon.remove();
          textErrorModal = document.createElement('h3');
          textErrorModal.innerHTML = 'Un problème est survenu lors de la récupération des données.';
          textErrorModal.className = 'text-primary text-center';
          listNode.appendChild(textErrorModal);
        });
    });
  })
});
