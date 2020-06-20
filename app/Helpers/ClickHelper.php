<?php
namespace App\Helpers;
use App\Models\Click;
use App\Models\Link;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Http\Request;
use GeoIp2\Database\Reader;
use MaxMind\Db\Reader\InvalidDatabaseException;

class ClickHelper {

    /**
     * @param $ip string
     * @return \GeoIp2\Model\City|null
     * @throws AddressNotFoundException
     * @throws InvalidDatabaseException
     */
    static private function getGeo2($ip) {
        $record = null;
        $reader = new Reader(storage_path('app/GeoLite2-City.mmdb'), ['zh-CN']);
        $record = $reader->city($ip);

        return $record;
    }

    static private function getHost($url) {
        // Return host given URL; NULL if host is
        // not found.
        return parse_url($url, PHP_URL_HOST);
    }

    static public function recordClick(Link $link, Request $request) {
        /**
         * Given a Link model instance and Request object, process post click operations.
         * @param Link model instance $link
         * @return boolean
         */

        $ip = $request->ip();
        $referer = $request->server('HTTP_REFERER');

        $click = new Click;
        $click->link_id = $link->id;
        $click->ip = $ip;
        $click->referer = $referer;
        $click->referer_host = ClickHelper::getHost($referer);
        $click->user_agent = $request->server('HTTP_USER_AGENT');

        try {
            if ($geoRecord = self::getGeo2($ip)) {
                $click->country  = $geoRecord->country->isoCode;
                $click->city     = $geoRecord->city->names['zh-CN'];
                $click->province = $geoRecord->mostSpecificSubdivision->names['zh-CN'];
            }
        } catch (InvalidDatabaseException | AddressNotFoundException | \Exception  $e) {
            $click->country = "";
            $click->city = "";
            $click->province = "";
        }

        $click->save();

        return true;
    }
}
