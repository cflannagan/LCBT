<?php
class TotalCreditLines extends Filter {
   public function __construct() {
      $this->options = array(10, 20, 30);
   }

   public function apply($loan) {
      $current = $this->options[$this->current];
      if ($loan["Total CREDIT Lines"] > $current)
         return false;
      return true;
   }
}
?>
