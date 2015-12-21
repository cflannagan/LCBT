<?php
class CreditGrade extends Filter {
   public function __construct($current = null) {
      $this->current = $current;
      $this->options = array(
         'A',
         'AB',
         'ABC',
         'ABCD',
         'ABCDE',
         'ABCDEF',
         //'ABCDEFG',
         'B',
         'BC',
         'BCD',
         'BCDE',
         'BCDEF',
         //'BCDEFG',
         'C',
         'CD',
         'CDE',
         'CDEF',
         //'CDEFG',
         'D',
         'DE',
         'DEF',
         //'DEFG',
         'E',
         'EF',
         //'EFG',
         'F',
         //'FG',
         //'G'
      );
   }

   public function apply($loan) {
      $current = $this->options[$this->current];
      if (!preg_match("/^[{$current}]/", $loan["grade"]))
         return false;
      return true;
   }
}
?>
