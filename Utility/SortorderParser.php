<?php

namespace Coral\SiteBundle\Utility;

use Coral\SiteBundle\Exception\SortorderParserException;

class SortorderParser
{
    public static function parse(Finder $finder)
    {
        $fileName = $finder->getSortorderPath();

        $parsed = array();

        if(false !== $fileName)
        {
            $handle = @fopen($fileName, "r");

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
                if(!feof($handle))
                {
                    throw new \LogicException("Unexpected fgets() fail [$fileName]");
                }
                fclose($handle);

                if(!count($parsed))
                {
                    throw new SortorderParserException("Empty .sortorder file in: " . $finder->getPath());
                }

                return $parsed;
            }
        }
        else
        {
            $parsed = $finder->createSortorderFromFileList();
        }

        return $parsed;
    }
}