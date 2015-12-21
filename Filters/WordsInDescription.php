<?php
/**
  * >75 looks best
  */
class WordsInDescription extends Filter {
   public function __construct() {
      for ($i = 25; $i <= 200; $i += 50)
         $this->options[] = $i;
   }

   public function apply($loan) {
      $current = $this->options[$this->current];
      $words = str_word_count($loan['desc']);
      if ($words < $current)
         return false;
      return true;
   }
}
?>
