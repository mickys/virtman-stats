<?php
/**
 * This file is part of the PHP VirtMan package
 *
 * PHP Version 7.1
 * 
 * @category VirtManStats
 * @package  VirtManStats
 * @author   Micky Socaci <micky@nowlive.ro>
 * @license  https://github.com/mickys/virtman-stats/blob/master/LICENSE.md MIT
 * @link     https://github.com/mickys/virtman-stats/
 */
namespace VirtManStats;

/**
 * VirtManStats main class
 *
 * @category VirtManStats
 * @package  VirtManStats
 * @author   Micky Socaci <micky@nowlive.ro>
 * @license  https://github.com/mickys/virtman-stats/blob/master/LICENSE.md MIT
 * @link     https://github.com/mickys/virtman-stats/
 */
class VirtManStats
{
    /**
     * Library Version
     *
     * @var string
     */
    const VERSION = '0.1.0';

    /**
     * VirtMan
     *
     * VirtMan Constructor
     * 
     * @param string $remoteUrl Libvirt machine URI
     *
     * @return
     */
    public function __construct( string $remoteUrl )
    {

    }

    /**
     * Libvirt is Installed
     *
     * Checks if the Libvirt PHP bindings are installed
     *
     * @return boolean
     */
    public function libvirtIsInstalled()
    {
        return function_exists('libvirt_version');
    }


}
