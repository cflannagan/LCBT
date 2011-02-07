<?php
class LoanPurpose extends Filter {
   private static $set;

   public function __construct($current = null) {
      $this->current = $current;
      if (!self::$set) {
         $set = array(
            "other", "debt_consolidation", "educational", "credit_card", "car",
            "home_improvement", "small_business", "vacation", "moving", "wedding", 
            "house", "medical", "major_purchase", "renewable_energy"
         );

         //$options = array(
         //   "other", "debt_consolidation", "credit_card", "home_improvement", 
         //   "vacation", "moving", "wedding", "house", "renewable_energy"
         //);

         self::$set = $this->powerSet($set);
      }
      $this->options = self::$set;
   }

   public function apply($loan) {
      $current = $this->options[$this->current];
      if (!in_array($loan["Loan Purpose"], $current))
         return false;
      return true;
   }
}
?>
