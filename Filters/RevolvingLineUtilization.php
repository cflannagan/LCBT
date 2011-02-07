<?php
class RevolvingLineUtilization extends Filter {
   public function __construct() {
      for ($i = 5; $i <= 100; $i += 5)
         $this->options[] = $i;
   }

   public function apply($loan) {
      $current = $this->options[$this->current];
      if (substr($loan["Revolving Line Utilization"], 0, -1) > $current)
         return false;
      return true;
   }
}
?>
