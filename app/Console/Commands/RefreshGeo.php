<?php
/**
 * Created by PhpStorm.
 * User: varcher
 * Date: 2020/6/21
 * Time: 19:33
 */

namespace App\Console\Commands;

use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MaxMind\Db\Reader\InvalidDatabaseException;

class RefreshGeo extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'geo:refresh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Refresh click geo info by geo2 IP database.';

    public function fire()
    {
        // Get default service
        $rows = DB::table('clicks')
            ->where('country', null)
            ->orderBy('id', "asc")->get();

        $record = null;
        try {
            $reader = new Reader(storage_path('app/GeoLite2-City.mmdb'), ['zh-CN']);
        } catch (InvalidDatabaseException $e) {
            echo "Database error! ";
            echo $e->getMessage();
            return false;
        }

        $processCount = 0;
        $processSuccess = 0;
        $processFail = 0;
        foreach ($rows as $row) {
            if ($row->country == null) {
                $ip = $row->ip;
                try {
                    $geoRecord = $reader->city($ip);
                    DB::table('clicks')->where("id", $row->id)->update(
                        [
                            'country'   =>  $geoRecord->country->isoCode,
                            'city'      =>  $geoRecord->city->names['zh-CN'],
                            'province'  =>  $geoRecord->mostSpecificSubdivision->names['zh-CN'],
                        ]
                    );
                    $processSuccess ++;
                } catch (InvalidDatabaseException | AddressNotFoundException | \Exception  $e) {
                    DB::table('clicks')->where("id", $row->id)->update(
                        [
                            'country'   =>  "",
                            'city'      =>  "",
                            'province'  =>  "",
                        ]
                    );
                    $processFail ++;
                }
                $processCount ++;
                if ($processCount % 100 == 0) {
                    echo "Progressing: {$processCount} Processes, {$processSuccess} success, {$processFail} failed.\n";
                }
            }
        }
        echo "TOTAL: {$processCount} Processes, {$processSuccess} success, {$processFail} failed.\n";
        echo "done.";
    }

}