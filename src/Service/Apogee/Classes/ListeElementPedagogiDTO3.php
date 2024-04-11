<?php

namespace App\Service\Apogee\Classes;

class ListeElementPedagogiDTO3 {

    public string $codListeElp;
    public string $typListeElp;
    public string $libCourtListeElp;
    public string $libListeElp;
    public string $temSpecialTraitementPRC;
    public TableauElementPedagogiDTO3 $listElementPedagogi;

    public function __construct(
        string $codListeElp,
        string $typListeElp,
        string $libCourt,
        string $libelleLong,
        array $tableauCodeELP
    ){
        $this->codListeElp = $codListeElp;
        $this->typListeElp = $typListeElp;
        $this->libCourtListeElp = $this->prepareLibelle($libCourt, 25);
        $this->libListeElp = $this->prepareLibelle($libelleLong, 60);
        $this->listElementPedagogi = new TableauElementPedagogiDTO3($tableauCodeELP);
    }

    private function prepareLibelle(?string $txt, int $length = 25) : string {
        if($txt){
            $rules = "À > A; Ç > C; É > E; È > E; :: NFC;";
            $transliterator = \Transliterator::createFromRules($rules, \Transliterator::FORWARD);
            return mb_substr($transliterator->transliterate($txt), 0, $length);
        }else {
            return 'ERROR';
        }
    }
}
