function DecryptMessageDeSeizeALaFin()
    {
        $chaine = 'Oqidlwxqvhfrqgqrhoduulyhwhvwsrxuyrlutxhfdpdufkh'; //chaine à convertir
        $choix = '16'; //nombre de décalage de lettres
        $pos = '1';// 1 vers la droite, -1 vers la gauche
        $fin = '26';
        $mode = "1"; //cryptage
        //$test = $this->Cesar($chaine,$choix,$pos,$mode, $fin);
        return new Response($test);
    }
function DecryptMessageDeUnASept()
    {
        $chaine = 'Oqidlwxqvhfrqgqrhoduulyhwhvwsrxuyrlutxhfdpdufkh'; //chaine à convertir
        $choix = '1'; //nombre de décalage de lettres
        $pos = '1'; // -1 vers la gauche
        $fin = '7';
        $mode = "1"; //cryptage
        //$test = $this->Cesar($chaine,$choix,$pos,$mode, $fin);
        return new Response($test);
    }