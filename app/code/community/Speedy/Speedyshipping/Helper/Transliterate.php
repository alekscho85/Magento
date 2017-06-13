<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Transliterate
 *
 * @author killer
 */
class Speedy_Speedyshipping_Helper_Transliterate extends Mage_Core_Helper_Abstract{
    //put your code here
    
    
    /**
     * This method performs transliteration of various parts of the customer 
     * address
     * @param type $word
     * @return type
     */
    public function transliterate($word) {


        if ($this->isCyrillic($word)) {

            $table = array(
                'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
                'е' => 'e', 'ж' => 'j', 'з' => 'z', 'и' => 'i', 'й' => 'y',
                'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o',
                'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'ъ' => 'u',
                'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch', 'ш' => 'sh',
                'щ' => 'sht',  'ь' => 'y', 'ю' => 'yu', 'я' => 'q',
                'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D',
                'Е' => 'E', 'Ж' => 'J', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y',
                'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
                'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'Ъ' => 'U',
                'Ф' => 'F', 'Х' => 'H', 'Ц' => 'C', 'Ч' => 'Ch', 'Ш' => 'Sh',
                'Щ' => 'Sht',  'Ь' => 'Y', 'Ю' => 'Yu', 'Я' => 'Q'
            );
            
            $newTable = array_flip($table);
            
            return strtr($word, $newTable);
            
        } else {
            
            return $word;
            
        }
    }

    public function getLanguage($word){
        if($this->isCyrillic($word)){
            return 'BG';
        }else{
            return 'EN';
        }
    }
    
    
    
    /**
     * This method detects whether the input is cyrillic or not 
     * @param type $word
     * @return boolean
     */
    protected function isCyrillic($word) {
        if (preg_match('/[A-Za-z]/ui', $word)) {
            return FALSE;
        }

        return TRUE;
    }

}

?>
