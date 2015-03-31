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
                // @codeCoverageIgnoreStart
                if(!feof($handle))
                {
                    throw new \RuntimeException("Unexpected fgets() fail [$fileName]");
                }
                // @codeCoverageIgnoreEnd
                fclose($handle);

                if(!count($parsed))
                {
                    throw new SortorderParserException("Empty .sortorder file in: " . $finder->getPath());
                }

                return $parsed;
            }

            // @codeCoverageIgnoreStart
            throw new \RuntimeException("Unable to open [$fileName]");
            // @codeCoverageIgnoreEnd
        }
        else
        {
            $parsed = $finder->createSortorderFromFileList();
        }

        return $parsed;
    }
}