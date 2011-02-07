<?php
class DebtToIncomeRatio extends Filter {
   public function __construct() {
      for ($i = 10; $i <= 30; $i += 5)
         $this->options[] = $i;
   }

   public function apply($loan) {
      $current = $this->options[$this->current];
      if (substr($loan["Debt-To-Income Ratio"], 0, -1) > $current)
         return false;
      return true;
   }
}
?>
