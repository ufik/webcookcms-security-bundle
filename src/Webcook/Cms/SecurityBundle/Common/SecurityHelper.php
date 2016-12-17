<?php

/**
 * This file is part of Webcook security bundle.
 *
 * See LICENSE file in the root of the bundle. Webcook 
 */

namespace Webcook\Cms\SecurityBundle\Common;

use Symfony\Component\Finder\Finder;

/**
 * Basic helper class.
 */
class SecurityHelper
{
    /**
     * Get system resources name.
     *
     * @param string $rootDir Root directory path.
     *
     * @return array Array of system resources.
     */
    public static function getResourcesNames($path)
    {
        $resources = array();
        $finder = new Finder();
        try {
            $finder->files()->in($path)->name('*Controller.php');
        } catch (\InvalidArgumentException $e) {
            return $resources;
        }

        $added = 0;
        foreach ($finder as $file) {
            $fileContent = file_get_contents($file->getRealPath());
            if (strpos($fileContent, 'extends BaseRestController') !== false
            && strpos($fileContent, 'implements PublicControllerInterface') === false) {
                $resources[] = self::extractName($file->getRealPath());
            }
        }

        return $resources;
    }

    /**
     * Extract name from the controller path string.
     *
     * @param string $controllerPath Path of the controller.
     *
     * @return string Name.
     */
    public static function extractName($controllerPath)
    {
        $controllerPath = str_replace('\\', '/', $controllerPath);

        return self::extract($controllerPath, 'Bundle').' - '.self::extract($controllerPath, 'Controller');
    }

    /**
     * Extract string.
     *
     * @param string $string String from which will be name extraced.
     * @param string $name   Extracted string.
     *
     * @return string Name.
     */
    private static function extract($string, $name)
    {
        preg_match("/^.*\/(.*)$name.*$/", $string, $matches);

        return count($matches) > 0 ? $matches[1] : null;
    }
}
