<?php
/**
  * TX and NY are good.
  *
  * States with substantial volume and higher-than-average returns:
  * CA, AZ, FL, GA, IL, MD, MO, NV, 
  */
class State extends Filter {
   public function __construct($current = null) {
      $this->current = $current;
      $this->options = array(
         //array("CA", "AZ", "FL", "GA", "IL", "MD", "MO", "NV")
         "AK", "AL", "AR", "AZ", "CA", "CO", "CT", "DC", "DE", "FL", "GA",
         "HI", "IA", "ID", "IL", "IN", "KS", "KY", "LA", "MA", "MD", "ME",
         "MI", "MN", "MO", "MS", "MT", "NC", "NE", "NH", "NJ", "NM", "NV",
         "NY", "OH", "OK", "OR", "PA", "RI", "SC", "SD", "TN", "TX", "UT",
         "VA", "VT", "WA", "WI", "WV", "WY", "NULL"
      );

      $this->options = $this->powerSet($options);
   }

   public function apply($loan) {
      if (is_null($loan["addr_state"]))
         $loan["addr_state"] = "NULL";

      $current = $this->options[$this->current];

      if (is_array($current)) {
         if (in_array($loan["addr_state"], $current))
            return false;
      }
      else {
         if ($loan["addr_state"] == $current)
            return false;
      }

      return true;
   }
}
?>
