<?php namespace dwalker109\Countdown;

class Rpn
{

    /**
     * Evaluates an RPN (postfix) expression - POC, only supports integer numbers
     * and +, -, * and / operators, tokens must be seperated with spaces
     * @param  string|Array $expression
     * @param  integer $target
     * @return integer|boolean
     */
    public static function calculate($expression, $target = null)
    {
        // Ensure expression is an array and remove any invalid values, to
        // guarantee we are working on only numbers and operators later (this
        // ensures that use of eval() is safe)
        $expression = is_array($expression) ? $expression : explode(' ', $expression);
        $expression = array_filter($expression, function ($value) {
            return preg_match('/[\+\-\/\*]|[\d]+/', $value) === 1 ? true : false;
        });

        // Define and process the RPN stack - operators will pop the last
        // two items off, apply the operator and return to the stack, whereas
        // items will just be appended to the stack
        $rpn_stack = new \SplStack();
        foreach ($expression as $e) {
            if (is_numeric($e)) {
                // Integer value, push to the stack
                $rpn_stack->push($e);
            } else {
                // Operator, pop the values off the stack
                $x = $rpn_stack->pop();
                $y = $rpn_stack->pop();

                // Exit if about to divide by zero (cannot catch)
                if ($e === '/' && ($y === 0 || $x === 0)) {
                    return false;
                }

                // eval() the result and push to the stack
                $rpn_stack->push(eval("return {$y} {$e} {$x};"));

                // Check if $target has been reached already - short circuit if so
                if ($rpn_stack->top() === $target) {
                    break;
                }
            }
        }

        // Finished, return the top of the stack
        return (int) $rpn_stack->top();
    }
}
