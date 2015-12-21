<?php
class EarliestCreditLine extends Filter {
   public function __construct($current = null) {
      $this->current = $current;
      $this->options = array(
         1, 5, 10
      );
   }

   public function apply($loan) {
      $current = $this->options[$this->current];
      $ecl = (time() - strtotime($loan["earliest_cr_line"]))
       / 60 / 60 / 24 / 365;
      if ($ecl < $current)
         return false;
      return true;
   }
}
?>
