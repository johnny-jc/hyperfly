<?php
declare(strict_types=1);

namespace Api\ApiBase;

use Hyperf\Database\Query\Builder;
use Hyperf\DbConnection\Model\Model;

/**
 * Class BaseActiveRecord
 * @package Api\ApiBase
 */
class BaseActiveRecord extends Model
{
    /**
     * BaseActiveRecord constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        //添加andFilterWhere()方法
        Builder::macro('andFilterWhere', function ($key, $operator, $value) {
            if ($value === '' || $value === '%%' || $value === '%') {
                return $this;
            }
            return $this->where($key, $operator, $value);
        });
        //添加orFilterWhere()方法
        Builder::macro('orFilterWhere', function ($key, $operator, $value) {
            if ($value === '' || $value === '%%' || $value === '%') {
                return $this;
            }
            return $this->orWhere($key, $operator, $value);
        });
        //添加getRawSql()方法
        Builder::macro('getRawSql', function () {
            $sql = str_replace(['%', '?'], ['%%', '%s'], $this->toSql());

            $handledBindings = array_map(function ($binding) {
                if (is_numeric($binding)) {
                    return $binding;
                }

                $value = str_replace(['\\', "'"], ['\\\\', "\'"], $binding);

                return "'{$value}'";
            }, $this->getConnection()->prepareBindings($this->getBindings()));

            return vsprintf($sql, $handledBindings);
        });
        //添加dumpSql()方法
        Builder::macro('dumpSql', function () {
            echo '================DUMP SQL START=========================' . PHP_EOL . PHP_EOL;
            echo $this->getRawSql() . PHP_EOL . PHP_EOL;
            echo '================DUMP SQL END=========================' . PHP_EOL . PHP_EOL;
        });
        parent::__construct($attributes);
    }

}