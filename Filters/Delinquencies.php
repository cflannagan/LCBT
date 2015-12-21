<?php
class Delinquencies extends Filter {
   public function __construct() {
      $this->options = array(
         array("", "0"), // 0
         array("", "0", "1", "2", "3"), // 0, 1-3
         array("", "0", "1", "2", "3", "4"), // 0, 1-3, 4
         array("1", "2", "3"), // 1-3
         array("1", "2", "3", "4"), // 1-3, 4
         array("4") // 4
      );
   }

   public function apply($loan) {
      $current = $this->options[$this->current];
      if (!in_array($loan["delinq_2yrs"], $current))
         return false;
      return true;
   }
}
?>
