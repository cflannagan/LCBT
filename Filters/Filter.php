<?php
class Filter {
   public $current = null;
   public $options = array();

   public function getCount() {
      return count($this->options);
   }

   public function next() {
      if (is_null($this->current))
         $this->current = 0;
      else if (++$this->current == $this->getCount())
         $this->current = null;

      return !is_null($this->current);
   }

   public function powerSet($options) {
      $count = count($options); 
      $members = pow(2, $count); 
      $result = array(); 

      for ($i = 0; $i < $members; $i++) { 
         $b = sprintf("%0" . $count . "b", $i); 
         $out = array(); 

         for ($j = 0; $j < $count; $j++)
            if ($b{$j} == '1')
               $out[] = $options[$j]; 

         $result[] = $out; 
      } 

      return $result; 
   }

   public static function increment(&$filters) {
      $n = 1;

      $num = count($filters);
      while ($filters[$num - $n] && !$filters[$num - $n]->next())
         $n++;

      return $filters[$num - $n];
   }

   public function getCurrent() {
      if (is_null($this->current))
         return null;
      return $this->options[$this->current];
   }

   public function details() {
      echo get_called_class() . " ({$this->current})\n";
      var_dump($this->getCurrent());
      echo "\n";
   }
}
?>
