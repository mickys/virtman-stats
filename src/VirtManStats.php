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
     * @var Configuration
     */
    private $config = null;

    /**
     * @var Configuration
     */
    private $libvirtConnections = null;

    /**
     * VirtMan
     *
     * VirtMan Constructor
     * 
     * @param array $config Config
     *
     * @return
     */
    public function __construct( array $config )
    {
        $this->runChecks();
        $this->config = $config;
    }

    public function connectNodes()
    {
        foreach($this->config["servers"] as $server) {
            $this->libvirtConnections[$server["name"]] = libvirt_connect($server["url"], $readonly = true, $credentials = array());
        }
    }

    public function gatherNodeData()
    {
        $nodes = [];
        foreach($this->config["servers"] as $server) {
            $connection = $this->libvirtConnections[$server["name"]];
            $node = [
                "libvirt" => libvirt_connect_get_information($connection),
                "hardware" => libvirt_node_get_info($connection),
                // "cpu_stats" => libvirt_node_get_cpu_stats($connection),
                "mem_stats" => libvirt_node_get_mem_stats($connection),
                // "libvirt_connect_get_machine_types" => libvirt_connect_get_machine_types($connection),
                // "libvirt_connect_get_sysinfo" => libvirt_connect_get_sysinfo($connection),
                // "libvirt_domain_get_counts" => libvirt_domain_get_counts($connection),
                // "libvirt_list_domains" => libvirt_list_domains($connection),
                // "libvirt_list_domain_resources" => libvirt_list_domain_resources($connection),
                // "libvirt_list_nodedevs" => libvirt_list_nodedevs($connection),

                // "libvirt_connect_get_all_domain_stats" => libvirt_connect_get_all_domain_stats($connection, VIR_DOMAIN_STATS_CPU_TOTAL),
            ];

            // VIR_DOMAIN_STATS_STATE
            // VIR_DOMAIN_STATS_VCPU
            // VIR_DOMAIN_STATS_CPU_TOTAL


            $machines = [];

            $data = libvirt_connect_get_all_domain_stats($connection, 0);
            foreach($data as $key => $values) {

                $machines[$key] = [];
                $machines[$key]["name"] = $key;
                $machines[$key]["state"] = [
                    "state" => $values["state.state"],
                    "reason" => $values["state.reason"],
                ];

                $machines[$key]["cpus"] = $values["vcpu.maximum"];

                if(isset($values["cpu.time"])) {
                    $machines[$key]["cpu"]["time"] = $values["cpu.time"];
                } else {
                    $machines[$key]["cpu"]["time"] = 0;
                }
                if(isset($values["cpu.user"])) {
                    $machines[$key]["cpu"]["user"] = $values["cpu.user"];
                } else {
                    $machines[$key]["cpu"]["user"] = 0;
                }
                if(isset($values["cpu.system"])) {
                    $machines[$key]["cpu"]["system"] = $values["cpu.system"];
                } else {
                    $machines[$key]["cpu"]["system"] = 0;
                }
                if(isset($values["cpu.cache.monitor.count"])) {
                    $machines[$key]["cpu"]["cache_count"] = $values["cpu.cache.monitor.count"] ;
                } else {
                    $machines[$key]["cpu"]["cache_count"] = 0;
                }


                $machines[$key]["memory"] = [];
                $machines[$key]["memory"]["capacity"] = $values["balloon.maximum"];
                
                if(isset($values["balloon.unused"])) {
                    $allocation = $machines[$key]["memory"]["capacity"] - $values["balloon.usable"];
                    $machines[$key]["memory"]["usage_percent"] = ($allocation / $machines[$key]["memory"]["capacity"]) * 100;
                } else {
                    $machines[$key]["memory"]["usage_percent"] = 0;
                }

                $machines[$key]["network"] = [];
                if(isset($values["net.count"])) {
                    $machines[$key]["network"]["count"] = $values["net.count"];
                    $machines[$key]["network"]["interfaces"] = [];
                    for($i = 0; $i < $values["net.count"]; $i++) {
                        $machines[$key]["network"]["interfaces"][] = [
                            "name" => $values["net.".$i.".name"],
                            "rx.bytes" => $values["net.".$i.".rx.bytes"],
                            "rx.pkts" => $values["net.".$i.".rx.pkts"],
                            "rx.errs" => $values["net.".$i.".rx.errs"],
                            "rx.drop" => $values["net.".$i.".rx.drop"],
                            "tx.bytes" => $values["net.".$i.".tx.bytes"],
                            "tx.pkts" => $values["net.".$i.".tx.pkts"],
                            "tx.errs" => $values["net.".$i.".tx.errs"],
                            "tx.drop" => $values["net.".$i.".tx.drop"],
                        ];
                    }
                }

                $machines[$key]["disk"] = [];
                $machines[$key]["disk"]["count"] = $values["block.count"];
                $machines[$key]["disk"]["drives"] = [];
                $allocation = 0;
                $capacity = 0;
                for($i = 0; $i < $values["block.count"]; $i++) {

                    if(isset($values["block.".$i.".path"])) {
                        $allocation+=$values["block.".$i.".allocation"];
                        $capacity+=$values["block.".$i.".capacity"];
                    
                        $machines[$key]["disk"]["drives"][$i]["name"] = $values["block.".$i.".name"];
                        $machines[$key]["disk"]["drives"][$i]["path"] = $values["block.".$i.".path"];
                        $machines[$key]["disk"]["drives"][$i]["allocation"] = $values["block.".$i.".allocation"];
                        $machines[$key]["disk"]["drives"][$i]["capacity"] = $values["block.".$i.".capacity"];
                        if(isset($values["block.".$i.".rd.bytes"])) {
                            $machines[$key]["disk"]["drives"][$i]["rd.bytes"] = $values["block.".$i.".rd.bytes"];
                            $machines[$key]["disk"]["drives"][$i]["wr.bytes"] = $values["block.".$i.".wr.bytes"];
                        }
                    }

                }
                $machines[$key]["disk_stats"] = [
                    "used" => $allocation,
                    "capacity" => $capacity,
                    "usage_percent" => ($allocation / $capacity) * 100,
                ];
            }

            // usage % = 100 * (cpu_time 2 - cpu_time 1) / N
            // guest_time = sum(vcpuX)=>cpu.time - sum(vcpuX)=>(for each child: cpuacct.stat=>user + cpuacct.stat=>system)
            // print_r($data["win10-micky"]);
            // print_r($machines["win10-micky"]);

            
            usort($machines, array(__CLASS__, "cmp"));
            $node["machines"] = $machines;
            $nodes[] = $node;
        }

        return $nodes;
    }

    static function cmp($a, $b)
    {
        return strcmp($a["state"]["state"], $b["state"]["state"]);
    }

    public function getDomainList()
    {
        foreach($this->config["servers"] as $server) {
            $connection = $this->libvirtConnections[$server["name"]];
            $data = libvirt_connect_get_information($connection);
            print_r($data);
        }
    }


    /**
     * Checks if the requirements are installed
     *
     * @return boolean
     */
    public function runChecks()
    {
        if(!function_exists('libvirt_version')) {
            die("Install php-libvirt module. Minimum version 0.5.6".PHP_EOL);
        }

        $versionData = libvirt_version();
        $intver = $versionData["connector.major"].$versionData["connector.minor"].$versionData["connector.release"];
        if((int) $intver >= 056) {
            return true;
        }
        die("Found php-libvirt module. Minimum version 0.5.6 / Found: ".$versionData["connector.version"].PHP_EOL);
    }


    /**
     * Get libvirt version
     *
     * Returns libvirt version to the connected node
     *
     * @return boolean
     */
    public function getLibvirtVersion()
    {
        return libvirt_version();
    }

}
