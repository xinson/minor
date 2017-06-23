<?php
/*
 * Phake - Mocking Framework
 *
 * Copyright (c) 2010-2012, Mike Lively <m@digitalsandwich.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *  *  Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *  *  Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *  *  Neither the name of Mike Lively nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Testing
 * @package    Phake
 * @author     Mike Lively <m@digitalsandwich.com>
 * @copyright  2010 Mike Lively <m@digitalsandwich.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @link       http://www.digitalsandwich.com/
 */

/**
 * An adapter class allowing PHPUnit constraints to be treated as though they were Phake argument
 * matchers.
 */
class Phake_Matchers_PHPUnit6ConstraintAdapter extends Phake_Matchers_SingleArgumentMatcher
{
    /**
     * @var \PHPUnit\Framework\Constraint\Constraint
     */
    private $constraint;

    /**
     * @param \PHPUnit\Framework\Constraint\Constraint $constraint
     */
    public function __construct(\PHPUnit\Framework\Constraint\Constraint $constraint)
    {
        $this->constraint = $constraint;
    }

    /**
     * Executes the matcher on a given argument value.
     *
     * Forwards the call to PHPUnit's evaluate() method.
     *
     * @param mixed $argument
     * @throws Phake_Exception_MethodMatcherException
     */
    protected  function matches(&$argument)
    {
        try
        {
            $this->constraint->evaluate($argument, '');
        }
        catch (\PHPUnit\Framework\ExpectationFailedException $e)
        {
            $failure = $e->getComparisonFailure();
            if ($failure instanceof \PHPUnit\Framework\ComparisonFailure)
            {
                $failure = $failure->getDiff();
            }
            else
            {
                $failure = '';
            }
            throw new Phake_Exception_MethodMatcherException($e->getMessage() . "\n" . $failure, $e);
        }
    }

    public function __toString()
    {
        return $this->constraint->toString();
    }
}
