import { Controller } from "@hotwired/stimulus";

export default class extends Controller {
    
    static targets = ['searchResult'];

    static values = {
        searchUrl: String
    };

    _minLength = 4;

    inputSearchEvent(event){
        if(event.target.value >= this._minLength){
            async () => {
                await fetch(`${this.searchUrlValue}?inputText=${event.target.value}`)
                    .then(response => response.json())
                    .then(json => console.log(json))
                    .catch(error => console.log(error));
            }
        }
    }
}