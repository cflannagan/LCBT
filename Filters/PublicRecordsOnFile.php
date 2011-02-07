<?php
class PublicRecordsOnFile extends Filter {
   public function __construct($current = null) {
      $this->current = null;
      $this->options = array(
         "Exclude loans with public records"
      );
   }

   public function apply($loan) {
      return !$loan["Public Records On File"];
   }
}
?>
