<?php
class MonthsSinceLastDelinquency extends Filter {
   public function __construct() {
      $this->options = array(
         12,
         24,
         60
      );
   }

   public function apply($loan) {
      $current = $this->options[$this->current];
      if ($loan["mths_since_last_delinq"] < $current)
         return false;
      return true;
   }
}
?>
