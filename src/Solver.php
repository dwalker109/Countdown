<?php namespace dwalker109\Countdown;

class Solver
{
    // Class vars for state tracking
    private $numbers;
    private $target;
    private $rpn_equations;
    private $results;


    public function __construct(Array $numbers, $target)
    {
        $this->numbers = $numbers;
        $this->target = $target;
        $this->rpn_equations = null;
        $this->results = null;
    }


    /**
     * Solve the problem
     * @return Array|bool
     */
    public function run()
    {
        // Generate the RPN expressions to allow brute force calculations
        $this->buildRpnExpressions($this->numbers);

        // Generate RPN equation results, then return them
        $this->calculateResults();
        return $this->results;
    }


    /**
     * Calculate all available RPN equations and store the results
     */
    private function calculateResults()
    {
        // Iterate all available equations and store winners locally
        $local_results = [];
        foreach ($this->rpn_equations as $rpn) {
            if (Rpn::calculate($rpn) === $this->target) {
                $local_results[] = [
                    'rpn' => $rpn,
                    'ifx' => Rpn::ConvertRpnToIfx($rpn),
                ];
            }
        }

        // Store winners in object, or false if there are none
        $this->results = $local_results ?: false;
    }


    /**
    * Build RPN equation strings for all permutations of the source numbers
    * (adapted from a Java example from the URL below)
    * @link   http://stackoverflow.com/a/2394972
    * @param  Array $numbers
    * @param  int $level
    * @param  string $equation
    */
    private function buildRpnExpressions(Array $numbers, $level = 0, $equation = null)
    {
        // At least two operands deep, so iterate and append each
        // operator to this tree in a recursive call to this method
        if ($level >= 2) {
            foreach (Rpn::$operators as $operator) {
                $this->buildRpnExpressions($numbers, $level - 1, $equation . Rpn::OP_TOK . $operator);
            }
        }

        // Iterate all source numbers once per tree - pass by ref so the current number can
        // be nullifed prior to the pool of numbers being used in the recursive call
        $all_used = true;
        foreach ($numbers as &$number) {
            if ($number !== null) {
                // Flag that all numbers have not been used, then iterate further down this tree
                // after nullifying current number in the passed pool, while incrementing depth
                $all_used = false;
                $n = $number;
                $number = null;
                $this->buildRpnExpressions($numbers, $level + 1, $equation . Rpn::OP_TOK . $n);
                // Restore current number for use with next iteration's tree
                $number = $n;
            }
        }

        // No numbers were left this time around and level is 1, so this tree is complete - store it
        if ($all_used && $level == 1) {
            $this->rpn_equations[] = trim($equation);
        }
    }
}
