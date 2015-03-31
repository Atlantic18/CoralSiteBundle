<?php

namespace Coral\SiteBundle\Utility;

class Finder
{
    private $path = null;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Get root path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Create a sortorder file content from file list within path
     *
     * @return array File list (excluding _ prefixed file)
     * @throws \InvalidArgumentException in case the folder is empty
     */
    public function createSortorderFromFileList()
    {
        if(file_exists($this->path))
        {
            $files = scandir($this->path, SCANDIR_SORT_ASCENDING);

            if(false !== $files)
            {
                $sortorder = array();

                foreach($files as $file)
                {
                    if(!is_dir($this->path . DIRECTORY_SEPARATOR . $file))
                    {
                        $prefix = substr($file, 0, 1);
                        if($prefix != '.' && $prefix != '_')
                        {
                            $sortorder[] = $file;
                        }
                    }
                }

                return $sortorder;
            }
        }
        throw new \InvalidArgumentException("Unable to read sortorder");
    }

    /**
     * Returns file name of sortorder no matter whether this file is _ or . prefixed.
     * Returns false if sortorder file doesn't exist.
     *
     * @param  string $path Path to content directory
     * @return string|boolean
     */
    public function getSortorderPath()
    {
        $fileName = $this->path . DIRECTORY_SEPARATOR . '.sortorder';
        if(file_exists($fileName))
        {
            return $fileName;
        }
        $fileName = $this->path . DIRECTORY_SEPARATOR . '_sortorder';
        if(file_exists($fileName))
        {
            return $fileName;
        }

        return false;
    }

    /**
     * Returns file name of properties no matter whether this file is _ or . prefixed.
     * Returns false if properties file doesn't exist.
     *
     * @param  string $path Path to content directory
     * @return string|boolean
     */
    public function getPropertiesPath()
    {
        $fileName = $this->path . DIRECTORY_SEPARATOR . '.properties';
        if(file_exists($fileName))
        {
            return $fileName;
        }
        $fileName = $this->path . DIRECTORY_SEPARATOR . '_properties';
        if(file_exists($fileName))
        {
            return $fileName;
        }

        return false;
    }
}