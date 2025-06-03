import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    
    static targets = [
        'selectedParcours',
        'searchForm',
        'searchInput',
        'searchResult',
        'emptySearchListButton',
        'selectAllParcoursButton',
        'loadingIcon',
        'needParcoursSelect',
        'needDataSelect'
    ];

    static values = {
        searchUrl: String,
        downloadPdfUrl: String,
        downloadXlsxUrl: String,
        campagneCollecte: Number
    };

    _minLength = 4;

    _selectedParcours = {};

    _selectedFields = {};

    connect(){
        this.searchFormTarget.addEventListener('submit', async (event)=> {
            event.preventDefault();
            await this.loadResultList();
        })

        document.querySelectorAll('.textDivFieldChoice')
            .forEach(choice => this.createListenerForFieldChoice(choice));
    }

    async fetchResults(searchText){
        let url = `${this.searchUrlValue}?inputText=${searchText}&campagneCollecte=${this.campagneCollecteValue}`;
        return await fetch(url)
            .then(response => response.json())
            .then(jsonData => jsonData)
            .catch(error => console.log(error));
    }

    async sendExportRequest(event){
        let isParcoursSelected = Object.keys(this._selectedParcours).length > 0;
        let isDataSelected = Object.keys(this._selectedFields).length > 0;

        if(isParcoursSelected && isDataSelected){
            // Champs souhaités
            let parcoursIdPostName = 'parcoursIdArray[]=';
            let fieldPostName = 'fieldValueArray[]=';

            let parcoursIdArray = [];
            // Construction des parcours voulus
            if(this._selectedParcours['all'] === true){
                parcoursIdArray.push("all");
            }
            else {
                for(const p in this._selectedParcours){
                    parcoursIdArray.push(p);
                }
            }
            // Construction des champs souhaités
            let fieldValueArray = [];
            for(const f in this._selectedFields){
                fieldValueArray.push(f);
            }

            let postParcours =  parcoursIdPostName + parcoursIdArray.join('&' + parcoursIdPostName);
            let postFields = fieldPostName + fieldValueArray.join('&' + fieldPostName)

            let type = event.params.type;

            let url = "#";
            let targetNewTab;

            if(type === 'pdf'){
                url = this.downloadPdfUrlValue + '?'
                    + postParcours + '&' + postFields
                    '&campagneCollecte=' + this.campagneCollecteValue;
                targetNewTab = '_blank';
            }
            else if (type === 'xlsx'){
                url = this.downloadXlsxUrlValue + '?'
                    + postParcours + '&' + postFields
                    '&campagneCollecte=' + this.campagneCollecteValue;
                targetNewTab = '_self';
            }

            this.needDataSelectTarget.classList.add('d-none');
            this.needParcoursSelectTarget.classList.add('d-none');

            window.open(url, targetNewTab);
        }
        else {
            if(!isParcoursSelected){
                this.needParcoursSelectTarget.classList.remove('d-none');
            }
            else {
                this.needParcoursSelectTarget.classList.add('d-none');
            }
            if(!isDataSelected){
                this.needDataSelectTarget.classList.remove('d-none');
            }
            else {
                this.needDataSelectTarget.classList.add('d-none');
            }
        }
        
    }

    async loadResultList(){
        if(this.searchInputTarget.value.length >= this._minLength){    
            this.emptyResultList();
            this.loadingIconTarget.classList.remove('d-none');
            let searchResult = await this.fetchResults(this.searchInputTarget.value);
            this.createResultList(searchResult);
            this.loadingIconTarget.classList.add('d-none');
            this.emptySearchListButtonTarget.classList.remove('d-none');
        } else {

        }
    }

    createResultList(resultArray){
        resultArray.forEach(parcours => {
            this.searchResultTarget.appendChild(
                this.createSearchResultNode(parcours.parcours_libelle, parcours.parcours_id)
            );
        });
    }

    createSearchResultNode(resultName, resultId){
        let row = document.createElement('div');
        row.classList.add('row', 'my-1');
        let textDiv = document.createElement('div');
        textDiv.classList.add('resultSearchNode');
        textDiv.dataset.parcoursId = resultId;
        textDiv.dataset.parcoursLibelle = resultName;
        textDiv.textContent = resultName;
        this.createClickListenerForListParcoursItem(textDiv);
        row.appendChild(textDiv);

        return row;
    }

    emptyResultList(){
        while(this.searchResultTarget.hasChildNodes()){
            this.searchResultTarget.removeChild(this.searchResultTarget.firstChild);
        }
    }

    emptyResultListEvent(){
        this.emptyResultList();
        this.emptySearchListButtonTarget.classList.add('d-none');
        if(this._selectedParcours['all'] !== true){
            this.selectAllParcoursButtonTarget.classList.remove('d-none');
        }
    }

    selectAllParcours(){
        this.emptyResultList();
        let row = document.createElement('div');
        row.classList.add('row', 'mt-3', 'allParcoursDiv');
        let textDiv = document.createElement('div');
        textDiv.classList.add('text-center', 'text-primary');
        textDiv.textContent = "Tous les parcours ont été sélectionnés";
        row.appendChild(textDiv);
        this.selectAllParcoursButtonTarget.classList.add('d-none');
        this.selectedParcoursTarget.appendChild(row);

        document.querySelectorAll('.badgeSelectedDiv').forEach(div => div.remove());
        this._selectedParcours = {'all': true};
    }

    createClickListenerForListParcoursItem(htmlNode){
        htmlNode.addEventListener('click', (event) => {
            if(this._selectedParcours[`${event.target.dataset.parcoursId}`] === undefined){
                if(this._selectedParcours['all'] !== undefined){
                    delete this._selectedParcours['all'];
                }
                this._selectedParcours[`${event.target.dataset.parcoursId}`] = event.target.dataset.parcoursLibelle;
                this.selectedParcoursTarget.appendChild(
                    this.createSelectedItemBadge(
                        event.target.dataset.parcoursLibelle,
                        event.target.dataset.parcoursId
                    )
                );
                this.selectedParcoursTarget.querySelector('.allParcoursDiv')?.remove();
            }
            // Affiche un message si aucun parcours sélectionné
            if(Object.keys(this._selectedParcours).length > 0){
                this.needParcoursSelectTarget.classList.add('d-none');
            }else {
                this.needParcoursSelectTarget.classList.remove('d-none');
            }
        })

    }

    createSelectedItemBadge(name, id){
        let badgeDiv = document.createElement('div');
        badgeDiv.classList.add('col-auto', 'my-2', 'badgeSelectedDiv');
        let textDiv = document.createElement('span');
        textDiv.classList.add('badge', 'rounded-pill', 'fw-bold', 'text-white', 'bg-primary');
        textDiv.textContent = name;
        textDiv.dataset.parcoursId = id;
        textDiv.dataset.parcoursLibelle = name;
        let crossIcon = document.createElement('i');
        crossIcon.classList.add('fa-regular', 'fa-xmark', 'text-white', 'ms-3');
        crossIcon.addEventListener('click', e => {
            this.removeSelectedItemBadge(id)
            // Affiche un message si aucun parcours sélectionné
            if(Object.keys(this._selectedParcours).length > 0){
                this.needParcoursSelectTarget.classList.add('d-none');
            }else {
                this.needParcoursSelectTarget.classList.remove('d-none');
            }   
        });
        textDiv.appendChild(crossIcon);
        badgeDiv.appendChild(textDiv);

        return badgeDiv;
    }

    removeSelectedItemBadge(id){
        if(this._selectedParcours[`${id}`] !== undefined){
            document.querySelectorAll('.badgeSelectedDiv')
                .forEach(badgeDiv => {
                    if(badgeDiv.querySelector('.badge.rounded-pill').dataset.parcoursId === `${id}`){
                        badgeDiv.remove();
                        delete this._selectedParcours[`${id}`];
                    }
                });
        }

    }

    createListenerForFieldChoice(node){
        node.addEventListener('click', (event) => {
            let badgeClassList = ['bg-info', 'text-white'];
            // Sélection
            if(this._selectedFields[event.target.dataset.exportField] === undefined){
                node.classList.add(...badgeClassList);
                this._selectedFields[event.target.dataset.exportField] = true;
            }
            // Déselection
            else if (this._selectedFields[event.target.dataset.exportField] !== undefined){
                node.classList.remove(...badgeClassList);
                delete this._selectedFields[event.target.dataset.exportField]
            }
            // Affiche un message si aucune donnée sélectionnée
            if(Object.keys(this._selectedFields).length > 0){
                this.needDataSelectTarget.classList.add('d-none');
            } else {
                this.needDataSelectTarget.classList.remove('d-none');
            }
        });
    }
}