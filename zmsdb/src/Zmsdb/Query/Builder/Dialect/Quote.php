<?php

namespace BO\Zmsdb\Query\Builder\Dialect;

use BO\Zmsdb\Query\Builder\ExpressionInterface;

/**
 * Quote
 *
 * Abstracts away the quoting mechanism so that you can simply say
 * which quote marks to use. This trait handles things like guarding
 * against double-quoting things.
 *
 * @package     BO\Zmsdb\Query\Builder\Dialect
 * @author      Alex Gisby<alex@solution10.com>
 * @license     MIT
 */
trait Quote
{
    /**
     * Takes a period-separated string and puts the appropriate quote
     * marks around it, guarding against double quoting.
     *
     * @param   string|ExpressionInterface  $string
     * @param   string                      $quoteMark      Mark to use for start and end of quotes
     * @param   array                       $unquotable     Any strings that mustn't be quoted (ie *)
     * @return  string
     */
    protected function quoteStructureParts($string, $quoteMark, array $unquotable = [])
    {
        if ($string instanceof ExpressionInterface) {
            return $string;
        }

        $string = trim($string);
        if ($string === null || strlen($string = trim($string)) === 0) {
            return $string;
        }

        $parts = explode('.', $string);
        $rebuild = [];
        foreach ($parts as $p) {
            $p = trim($p);
            if (!in_array($p, $unquotable) && $p != '') {
                // quote at the front
                if (strpos($p, $quoteMark) !== 0) {
                    $p = $quoteMark . $p;
                }
                // quote at the back:
                if (strrpos($p, $quoteMark) != (strlen($p) - 1)) {
                    $p .= $quoteMark;
                }
            }

            if (trim($p) != '') {
                $rebuild[] = $p;
            }
        }

        return implode('.', $rebuild);
    }
}
