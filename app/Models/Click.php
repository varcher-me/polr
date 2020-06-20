<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string ip
 * @property string country
 * @property string referer
 * @property string referer_host
 * @property string user_agent
 * @property integer link_id
 * @property string created_at
 * @property string updated_at
 * @property string city
 * @property string province
 */
class Click extends Model {
    protected $table = 'clicks';
}
