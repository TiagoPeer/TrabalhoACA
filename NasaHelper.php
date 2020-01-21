<?php
//NasaHelper.php
//https://apod.nasa.gov/apod/ap950616.html
//https://apod.nasa.gov/apod/ap191106.html

class NasaHelper{
    const MARK_IMG_START = "<IMG SRC=\"";

    const APOD_URL_BASE = "https://apod.nasa.gov/apod/";
    const APOD_URL_IMAGE_BASE = "https://apod.nasa.gov/";

    private $mTimeZone;

    public function __construct(
        $pTimeZone = false
    ){
        $this->mTimeZone = $pTimeZone ?
            "Europe/Lisbon"
            :
            $pTimeZone;

        //date_default_timezone_set($this->mTimeZone);
    }//__construct

    /*
     * usando CURL - "Consume URL"
     */
    public static function auxConsumeUrl(
        string $pUrl
    ){
        //obter um CURL "handler" que é um ponteiro para um objecto
        //com o qual poderemos fazer operações de consumo na Internet
        $ch = curl_init(); //inicialização
        if ($ch){
            //opções
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_URL, $pUrl);
            curl_setopt($ch, CURLOPT_USERAGENT, "My bot");

            //fazer acontecer
            $bin = curl_exec($ch);
            return $bin;
        }//if
        return false;
    }//auxConsumeUrl

    public static function auxIntToString(
        int $pInt,
        int $pNDigits
    ) : string {
        $strInt = $pInt."";
        $iHowManyDigits = strlen($strInt);
        $bCanConvertWithoutLoss = $iHowManyDigits<=$pNDigits;
        if ($bCanConvertWithoutLoss){
            $ret = $strInt;
            while (strlen($ret)<$pNDigits) $ret = "0".$ret;
            return $ret;
        }//if
        else{
            //é necessário cortar símbolos
            $iHowManyToCut = $iHowManyDigits-$pNDigits;
            $ret = substr($strInt, $iHowManyToCut);
            return $ret;
        }//else
    }//auxIntToString

    const KEY_YEAR = "KEY_YEAR", KEY_MONTH="KEY_MONTH", KEY_DAY="KEY_DAY";
    public static function auxTimeStampToDateParts(
        $pTimeStamp = false
    ){
        $timeStamp = $pTimeStamp===false ? time() : $pTimeStamp;
        $strTime = date("Y-m-d", $timeStamp); //"2019-11-06"
        $aTimeParts = explode("-", $strTime);
        $bCaution = is_array($aTimeParts) && count($aTimeParts)===3;
        if ($bCaution){
            $strY = $aTimeParts[0];
            $strM = $aTimeParts[1];
            $strD = $aTimeParts[2];
            @$ret[KEY_YEAR] = intval($strY);
            @$ret[KEY_MONTH] = intval($strM);
            @$ret[KEY_DAY] = intval($strD);
            return $ret;
        }//if
        return false;
    }//auxTimeStampToDateParts

    public static function urlForDay(
        $pY = false, $pM = false, $pD = false
    ){
        $bSomeArgumentMissing = $pY===false || $pM===false || $pD===false;
        if ($bSomeArgumentMissing){
            //sem todos os componentes da data, recorro à data do sistema
            $myDate = self::auxTimeStampToDateParts();
        }
        else{
            //se tenho todos os componentes da data
            $timeStamp = mktime(0, 0, 0, $pM, $pD, $pY);
            $myDate = self::auxTimeStampToDateParts($timeStamp);
        }
        //em posse da data argumentada, segmento-a
        @$y = $myDate[self::KEY_YEAR]; //2018
        @$m = $myDate[self::KEY_MONTH]; //12
        @$d = $myDate[self::KEY_DAY]; //1
        $strYear = self::auxIntToString($y, 2); //"18"
        $strMonth = self::auxIntToString($m, 2); //"12"
        $strDay = self::auxIntToString($d, 2); //"01"
        $url = sprintf(
            "%sap%s%s%s.html",
            self::APOD_URL_BASE,
            $strYear,
            $strMonth,
            $strDay
        );
        return $url;
    }//urlForDay

    /*
     * esta ferramenta se NÃO receber argumentos
     * retorna o URL direto para a imagem do dia de "hoje",
     * se receber argumentos
     * retorna o URL direto para a imagem do dia argumentado
     *
     * se falhar (por exemplo, porque não há imagem), retorna false
     */
    public static function getDirectUrlForIod (
        $pY = false, $pM = false, $pD = false
    )
    {
        $strUrl = self::urlForDay($pY, $pM, $pD); //html
        $strHtmlSrcCode = self::auxConsumeUrl ($strUrl);
        $iImgStart = stripos($strHtmlSrcCode, self::MARK_IMG_START);
        if ($iImgStart!==false){
            $strRelevantPartStart = substr(
                $strHtmlSrcCode,
                $iImgStart + strlen(self::MARK_IMG_START)
            );

            $strImgRelativeUrl = substr(
                $strRelevantPartStart,
                0,
                stripos($strRelevantPartStart, "\"")
            );

            //return $strImgRelativeUrl;
            return self::APOD_URL_IMAGE_BASE.$strImgRelativeUrl;
        }
        else{
            return false; //não há imagem na página em causa
        }
        echo $strHtmlSrcCode;
    }//getDirectUrlForIod

    public static function downloadAndDisplay (
        $pY = false, $pM = false, $pD = false
    ){
        $strDirectUrlForTheIod = self::getDirectUrlForIod($pY, $pM, $pD);
        $bin = self::auxConsumeUrl($strDirectUrlForTheIod);
        $strFilename = substr(
            $strDirectUrlForTheIod,
            strripos($strDirectUrlForTheIod, "/")+1
        );
        echo $strFilename;

        $iBytesWrittenOrFalse =
            file_put_contents(
                $strFilename, //TO DO
                $bin
            );
        //shell_exec("dump.jpg");
        return $strFilename;
    }//downloadAndDisplay
}//NasaHelper

$helper = new NasaHelper();
