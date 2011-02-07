<?php
/**
  * People with mortgages suck at repaying LC loans.
  */
class HomeOwnership extends Filter {
   public function __construct($current = null) {
      $this->current = $current;
      $this->options = array(
         //"MORTGAGE"
         array("MORTGAGE", "OWN"),
         "RENT",
         "MORTGAGE",
         "OWN",
         "NULL"
      );
   }

   public function apply($loan) {
      if (is_null($loan["Home Ownership"]))
         $loan["Home Ownership"] = "NULL";

      $current = $this->options[$this->current];
      if ($loan["Home Ownership"] == $current)
         return false;
      return true;
   }
}
?>
