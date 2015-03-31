<?php

namespace Coral\SiteBundle\Utility;

use Coral\SiteBundle\Exception\PropertiesParserException;

class PropertiesParser
{
    public static function parse(Finder $finder)
    {
        $fileName = $finder->getPropertiesPath();

        if(false === $fileName)
        {
            return $finder->createSortorderFromFileList();
        }

        $handle   = @fopen($fileName, "r");
        $parsed   = array('name' => '', 'properties' => array());

        if($handle)
        {
            while(($buffer = fgets($handle, 4096)) !== false)
            {
                $buffer = trim($buffer);
                if($buffer)
                {
                    if(!$parsed['name'])
                    {
                        $parsed['name'] = $buffer;
                    }
                    else
                    {
                        if(($pos = strpos($buffer, ':')) !== false)
                        {
                            $key = trim(substr($buffer, 0, $pos));
                            $value = trim(substr($buffer, $pos + 1));

                            if($value[0] == '"' && substr($value, -1) != '"')
                            {
                                throw new PropertiesParserException("Invalid quotation when parsing key:value in [$fileName]");
                            }

                            if(!preg_match('/^[a-z0-9_]+$/i', $key))
                            {
                                throw new PropertiesParserException("Invalid key [$key] when parsing key:value in [$fileName]");
                            }

                            $parsed['properties'][$key] = trim($value, '"');
                        }
                        else
                        {
                            throw new PropertiesParserException("Missing ':' when parsing key:value in [$fileName]");
                        }
                    }
                }
            }
            if(!$parsed['name'])
            {
                throw new PropertiesParserException("Missing first line in properties in [$fileName]");
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