<?php
error_reporting(0);
ini_set('memory_limit', '4G');
ini_set('max_execution_time', 86400*7);

require 'Filters.php';

// Print detailed information about a filter list
$filters = array(
   new AmountRequested(),
   new CreditGrade(),
   new DebtToIncomeRatio(),
   new Delinquencies(),
   new EarliestCreditLine(),
   new HomeOwnership(),
   new Inquiries(),
   new LoanPurpose(),
   new MonthsSinceLastDelinquency(),
   new PublicRecordsOnFile(),
   new RevolvingLineUtilization(),
   new State(),
   new TotalCreditLines(),
   new WordsInDescription()
);
if ($argv[1]) {
   $f = explode(",", $argv[1]);

   for ($i = 0; $i < count($f); $i++) {
      $filters[$i]->current =  $f[$i] != '' ? $f[$i] : null;
      echo $filters[$i]->details();
   }
   LCBT::initialize();
   $result = LCBT::test($filters);
   extract($result);

      echo "{$num_loans} loans ({$loans_per_month}/mo.) tested at {$expected_apy}% APY\n";
      echo "{$num_defaulted} loans defaulted ({$pct_defaulted}%, \$" .
       "{$avg_default_loss} avg loss)\n";
      echo "{$net_apy}% net APY\n\n";
   exit;
}

LCBT::initialize();
$ga_test = new GA_Test();
$ga_test->run();

class GA_Test {
   public $population = array();
   public static $population_size = 512;
   public static $iterations = 4096;
   public static $elite_rate = 0.05;
   public static $mutation_rate = 0.05;

   public function __construct()  {
      for ($i=0; $i<self::$population_size; $i++) {
         $filters = array(
            new AmountRequested(),
            new CreditGrade(),
            new DebtToIncomeRatio(),
            new Delinquencies(),
            new EarliestCreditLine(),
            new HomeOwnership(),
            new Inquiries(),
            new LoanPurpose(),
            new MonthsSinceLastDelinquency(),
            new PublicRecordsOnFile(),
            new RevolvingLineUtilization(),
            new State(),
            new TotalCreditLines(),
            new WordsInDescription()
         );

         foreach ($filters as $filter) {
            if (($filter->current = rand(0, $filter->getCount())) == $filter->getCount())
               $filter->current = null;
         }

         $this->population[] = array(
            'filters' => $filters
         );
      }
   }

   public function run() {
      for ($i=0; $i<self::$iterations; $i++) {
         echo "Calculating fitness";
         $this->calculate_fitness();
         $this->sort_by_fitness();
         $this->print_best();

         $this->mate();
      }
   }

   public function calculate_fitness() {
      foreach ($this->population as &$citizen) {
         $citizen['results'] = LCBT::test($citizen['filters']);
         echo ".";
      }
      echo "\n";
   }

   public function sort_by_fitness() {
      usort($this->population, function($a, $b) {
         $a_fit = $a['results']['net_apy'];
         $b_fit = $b['results']['net_apy'];

         if ($a['results']['num_loans'] < 600)
            $a_fit = 0;
         if ($b['results']['num_loans'] < 600)
            $b_fit = 0;

         return $b_fit - $a_fit;
      });
   }

   public function print_best() {
      extract($this->population[0]['results']);

      $filters = '';
      foreach ($this->population[0]['filters'] as $filter)
         $filters .= $filter->current . ',';
      $filters = substr($filters, 0, -1);
      echo "{$filters}\n";

      echo "{$num_loans} loans ({$loans_per_month}/mo.) tested at {$expected_apy}% APY\n";
      echo "{$num_defaulted} loans defaulted ({$pct_defaulted}%, \$" .
       "{$avg_default_loss} avg loss)\n";
      echo "{$net_apy}% net APY\n\n";
   }

   public function mate() {
      // Save the elite!
      $num_elite = (int)(self::$elite_rate * self::$population_size);
      $final_population = array_slice($this->population, 0, $num_elite);

      for ($i=$num_elite; $i<self::$population_size; $i++) {
         $final_population[$i] = $this->population[$i];

         for ($j=0; $j<count($final_population[$i]['filters']); $j++) {
            // Mate with best 20% of population.
            $partner = rand(0, self::$population_size / 5);

            $final_population[$i]['filters'][$j]->current = $this->population[$partner]['filters'][$j]->current;

            // Mutate!
            if (!rand(0, 1/self::$mutation_rate)) {
               $filter = $final_population[$i]['filters'][$j];
               if (($filter->current = rand(0, $filter->getCount())) == $filter->getCount())
                  $filter->current = null;
            }
         }
      }
      
      $this->population = $final_population;
   }
}

class LCBT {
   private static $row = 0;
   private static $labels = false;
   private static $results = array();
   private static $filters = array();
   private static $loans = array();

   public static function initialize() {
      // Check in-memory cache.
      if (!count(self::$loans)) {
         // Check serialized file cache.
         $csv_mod = @filemtime("LoanStats.csv");
         $serialized_mod = @filemtime("loans.serialized");
         if ($serialized_mod && $serialized_mod > $csv_mod) {
            echo "Initializing from cache...";
            self::$loans = unserialize(file_get_contents("loans.serialized"));
            echo " done\n";
         }
         // double cache miss
         else if (($handle = fopen("LoanStats.csv", "r")) !== FALSE) {
            echo "Initializing from csv...";
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
               if (!self::getLabels($data))
                  continue;

               if (!($loan = self::getLoan($data)))
                  continue;

               self::$loans[] = $loan;
            }

            fclose($handle);

            file_put_contents("loans.serialized", serialize(self::$loans));
            echo " done\n";
         }
         else {
            die("LoanStats.csv not found.\n");
         }
      }
   }

   public static function test($filters) {
      self::initialize();

      self::$filters = $filters;
      $invested = array();

      $num = 0;
      foreach (self::$loans as $loan) {
         $invest = self::consider($loan);
         
         if ($invest) {
            $invested[] = $loan;
            $statuses[$loan["Status"]]++;
         }
      }

      return self::getNAR($invested);
   }

   public static function getNAR($invested) {
      $time = time();
      $profit = $principal = $defaulted = $lost = 0;

      foreach ($invested as $loan) {
         $elapsed = floor(($time - strtotime($loan["Issued Date"])) / 86400 / 30);
         $balance = $loan["Amount Borrowed"];
         $ratio = 25 / $balance;
         $payments = 0;

         while ($elapsed-- > 0) {
            // Interest and service charge for the whole loan (not just me)
            $interest = (substr($loan["Interest Rate"], 0, -1) / 1200) * $balance;
            $service = 0.01 * $loan["Monthly PAYMENT"];
            $payments += $loan["Monthly PAYMENT"];

            $default = self::defaultedAmount($loan);
            if ($default && $payments > $loan["Payments To Date"]) {
               $profit -= $default * $ratio;
               $lost += $default * $ratio;
               $defaulted++;
               break;
            }

            // Compute my ratio of the profit
            $profit += (($interest - $service) * $ratio);
            $principal += ($balance * $ratio);

            $balance -= $loan["Monthly PAYMENT"];
         }
      }

      $filters = '';
      foreach (self::$filters as $filter)
         $filters .= $filter->current . ',';
      $filters = substr($filters, 0, -1);

      $nar = @number_format(pow(1 + $profit / $principal, 12) - 1, 4) * 100;

      foreach ($invested as $loan)
         $rate += substr($loan["Interest Rate"], 0, -1);

      $expect = @number_format($rate / count($invested), 2);

      $default_percent =
       @number_format(($defaulted / count($invested)) * 100, 1);

      $num_loans = count($invested);
      $avg_loss = @number_format($lost / $defaulted, 2);

      // Count loan volume.
      $per_month = 0;
      foreach ($invested as $loan) {
         if (substr($loan['Issued Date'], 0, 7) == '2010-09')
            $per_month++;
      }

      return array(
         'filters' => $filters,
         'num_loans' => $num_loans,
         'loans_per_month' => $per_month,
         'expected_apy' => $expect,
         'num_defaulted' => $defaulted,
         'pct_defaulted' => $default_percent,
         'avg_default_loss' => $avg_loss,
         'net_apy' => $nar
      );
   }

   public static function defaultedAmount($loan) {
      if (in_array($loan["Status"], array("Charged Off", "Default")))
         return ($loan["Amount Borrowed"] - $loan["Payments To Date"]);
      return 0;
   }

   public static function consider($loan) {
      foreach (self::$filters as $filter)
         if (!is_null($filter->current) && !$filter->apply($loan))
            return false;
      return true;
   }

   public static function getLoan($data) {
      if (self::$row <= 2)
         return false;

      $loan = array();
      $cols = count($data);
      for ($c=0; $c<$cols; $c++)
         $loan[self::$labels[$c]] = $data[$c];

      if (!$loan["Status"] || !$loan["Amount Borrowed"])
         return false;

      // Only look at loans >3 months old
      if (!preg_match("/^2\d{3}-\d{2}-\d{2}$/", $loan["Issued Date"]))
         return false;
      if (strtotime($loan["Issued Date"]) > time() - 86400*30*3)
         return false;
      // Ignore loans that didn't even start
      if (in_array($loan["Status"], array("Removed", "Expired")))
         return false;
      return $loan;
   }

   public static function getLabels($data) {
      if (++self::$row == 2) {
         self::$labels = array();
         $cols = count($data);
         for ($c=0; $c<$cols; $c++)
            self::$labels[$c] = $data[$c];
      }
      return self::$labels;
   }
}
?>
