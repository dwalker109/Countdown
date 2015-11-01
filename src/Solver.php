<?php namespace dwalker109\Countdown;

class Solver
{
    // Class vars for state tracking
    private $numbers;
    private $target;
    private $rpn_equations;


    public function __construct(Array $numbers, $target)
    {
        $this->numbers = $numbers;
        $this->target = $target;
        $this->rpn_equations = null;
    }


    /**
     * Solve the problem
     * @return Array|bool
     */
    public function run()
    {
        $winners = [];

        // Generate the RPN expressions to allow brute force calculations
        $this->buildRpnEquations($this->numbers);

        // Carry out each calculation, recording the winners
        foreach ($this->rpn_equations as $rpn) {
            if (Rpn::calculate($rpn) === $this->target) {
                $winners[] = [
                    'rpn' => $rpn,
                    'ifx' => Rpn::ConvertRpnToIfx($rpn),
                ];
            }
        }

        // Return successful equations, or boolean false if no results
        return $winners ?: false;
    }


    /**
    * Build RPN equation strings for all permutations of the source numbers
    * (adapted from a Java example from the URL below)
    * @link   http://stackoverflow.com/a/2394972
    * @param  Array $numbers
    * @param  int $level
    * @param  string $equation
    */
    private function buildRpnEquations(Array $numbers, $level = 0, $equation = null)
    {
        if ($level >= 2) {
            foreach (Rpn::$operators as $operator) {
                $this->buildRpnEquations($numbers, $level - 1, $equation . Rpn::OP_TOK . $operator);
            }
        }

        $all_used = true;

        for ($i = 0; $i < count($numbers); $i++) {
            if ($numbers[$i]) {
                $all_used = false;
                $n = $numbers[$i];
                $numbers[$i] = null;
                $this->buildRpnEquations($numbers, $level + 1, $equation . Rpn::OP_TOK . $n);
                $numbers[$i] = $n;
            }
        }

        if ($all_used && $level == 1) {
            $this->rpn_equations[] = trim($equation);
        }
    }
}
