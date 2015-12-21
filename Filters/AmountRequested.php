<?php
class AmountRequested extends Filter {
   public function __construct($current = null) {
      $this->current = $current;
      for ($i = 5000; $i <= 25000; $i += 5000)
         $this->options[] = $i;
   }

   public function apply($loan) {
      $current = $this->options[$this->current];
      if ($loan["loan_amnt"] > $current)
         return false;
      return true;
   }
}
?>
