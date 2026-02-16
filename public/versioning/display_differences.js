let idDiffLastVersion = "diffGlobalParcours";
let idDiffLastYear = "diffGlobalParcoursCampagne";

let currentDisplay = undefined;

function registerShowDifferencesClick(buttonElement, idToShow, idToHide) {
    if(buttonElement){
        buttonElement.addEventListener('click', (event) => {
            event.preventDefault();
            if(currentDisplay !== idToShow){
                document.querySelectorAll(`#${idToShow}`)
                    .forEach(elt => {
                        elt.classList.remove('d-none');
                    });
                document.querySelectorAll(`#${idToHide}`)
                    .forEach(elt => {
                        elt.classList.add('d-none');
                    });
                currentDisplay = idToShow;
            }
            else {
                document.querySelectorAll(`#${idToShow}`)
                    .forEach(elt => {
                        elt.classList.add('d-none');
                    });
                currentDisplay = undefined;
            }
        });
    }
};

document.addEventListener('DOMContentLoaded', (e) => {
    let buttonShowDiffLastYear = document.querySelector("#showDiffWithLastYear");
    let buttonShowDiffLastVersion = document.querySelector("#showDiffWithLastVersion");
    registerShowDifferencesClick(buttonShowDiffLastVersion, idDiffLastVersion, idDiffLastYear);
    registerShowDifferencesClick(buttonShowDiffLastYear, idDiffLastYear, idDiffLastVersion);
})