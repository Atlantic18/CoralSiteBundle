<?php

namespace Coral\SiteBundle\Parser;

use Coral\SiteBundle\Exception\SortorderParserException;

class SortorderParser
{
    public static function parse($fileName)
    {
        $handle = @fopen($fileName, "r");
        $parsed = array();

        if($handle)
        {
            while(($buffer = fgets($handle, 4096)) !== false)
            {
                $buffer = trim($buffer);
                if($buffer)
                {
                    $parsed[] = $buffer;
                }
            }
            if(!count($parsed))
            {
                throw new SortorderParserException("Empty .sortorder file in [$fileName]");
            }
            if(!feof($handle))
            {
                throw new \LogicException("Unexpected fgets() fail [$fileName]");
            }
            fclose($handle);

            return $parsed;
        }

        throw new \InvalidArgumentException("Unable to read [$fileName]");
    }
}