<?php

declare (strict_types=1);
namespace Api\ApiBaseModel;

use Api\ApiActiveRecord\RbacAdminActiveRecord;
use Hyperf\Di\Annotation\Inject;
/**
 * Class RentModel
 * @package Api\ApiBaseModel
 */
class RentModel
{
    use \Hyperf\Di\Aop\ProxyTrait;
    use \Hyperf\Di\Aop\PropertyHandlerTrait;
    function __construct()
    {
        $this->__handlePropertyHandler(__CLASS__);
    }
    /**
     * @Inject
     * @var RbacAdminActiveRecord
     */
    private $model;
    public function insertSingle(array $data = [])
    {
        foreach ($data as $k => $v) {
            $this->model->{$k} = $v;
        }
        $this->model->save();
    }
}