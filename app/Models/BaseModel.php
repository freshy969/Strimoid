<?php namespace Strimoid\Models;

use Carbon\Carbon;
use Date;
use DateTimeZone;
use Eloquent;
use Hashids;
use Setting;
use Watson\Rememberable\Rememberable;

abstract class BaseModel extends Eloquent
{
    use Rememberable;

    /**
     * @var array Validation validationRules
     */
    protected static $rules = [];

    /**
     * Return a timestamp as DateTime object.
     *
     * @param mixed $value
     *
     * @return \Jenssegers\Date\Date
     */
    protected function asDateTime($value)
    {
        $value = parent::asDateTime($value);

        return Date::instance($value);
    }

    public function createdAgo() : string
    {
        return $this->created_at->diffForHumans();
    }

    /** Get formatted time, converted to current user timezone */
    public function getLocalTime() : string
    {
        $timezone = Setting::get('timezone');
        $timezone = new DateTimeZone($timezone);

        return $this->created_at->setTimeZone($timezone)->format('d/m/Y H:i:s');
    }

    public static function validationRules() : array
    {
        return static::$rules;
    }

    public function hashId() : string
    {
        return Hashids::encode($this->getKey());
    }

    public function getHashIdAttribute() : string
    {
        return $this->hashId();
    }

    public function getRouteKey() : string
    {
        return $this->hashId();
    }

    /* Scopes */

    /**
     * Filter by created time ago.
     *
     * @param $query
     * @param $days
     */
    public function scopeFromDaysAgo($query, $days)
    {
        $fromTime = Carbon::now()->subDays($days)
            ->hour(0)->minute(0)->second(0);
        $query->where('created_at', '>', $fromTime->toDateTimeString());
    }

    public function scopeUserCache($query, string $tag, int $minutes = 60)
    {
        $tags = ['user.'.$tag, 'u.'.auth()->id()];
        $query->remember($minutes)->cacheTags($tags);
    }
}
