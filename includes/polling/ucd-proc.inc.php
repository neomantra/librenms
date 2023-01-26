<?php
/**
 * ucd-proc.inc.php
 *
 * LibreNMS UCD-SNMP Process Management Support (prTable)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  Neomantra BV 2023
 * @author     Evan Wies <evan@neomantra.net>
 */

 use LibreNMS\RRD\RrdDefinition;

// Poll prTable from UNIX-like hosts running UCD/Net-SNMPd
// OID 1.3.6.1.4.1.2021.2
// UCD-SNMP-MIB::prIndex.1 = INTEGER: 1
// UCD-SNMP-MIB::prNames.1 = STRING: processName
// UCD-SNMP-MIB::prMin.1 = INTEGER: 1
// UCD-SNMP-MIB::prMax.1 = INTEGER: 0
// UCD-SNMP-MIB::prCount.1 = INTEGER: 1
// UCD-SNMP-MIB::prErrorFlag.1 = INTEGER: noError(0)
// UCD-SNMP-MIB::prErrMessage.1 = STRING: 
// UCD-SNMP-MIB::prErrFix.1 = INTEGER: noError(0)
// UCD-SNMP-MIB::prErrFixCmd.1 = STRING: 

$proc_table = [];
$proc_table = snmpwalk_cache_oid($device, 'prTable', [], 'UCD-SNMP-MIB');
foreach ($proc_table as $proc) {
    $tags = [
        'rrd_name'  => ['ucd_proc', $proc['prNames']],
        'rrd_def'   =>  RrdDefinition::make()
            ->addDataset('min', 'COUNTER', 0)
            ->addDataset('max', 'COUNTER', 0)
            ->addDataset('count', 'COUNTER', 0)
            ->addDataset('errorFlag', 'COUNTER', 0)
            ->addDataset('errorFix', 'COUNTER', 0),
        'desc'     => $proc['prNames'],
    ];
    $fields = [
        'min'       => $proc['prMin'],
        'max'       => $proc['prMax'],
        'count'     => $proc['prCount'],
        'errorFlag' => $proc['prErrorFlag'],
        'errorFix'  => $proc['prErrorFix'],
    ];

    data_update($device, 'ucd_proc', $tags, $fields);
}

unset($proc_table);
